<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Collector\Measurement;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Service\CryptService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Zeus\Client\InexogyClient;
use GibsonOS\Module\Zeus\Dto\InexogyConsumer;
use GibsonOS\Module\Zeus\Model\Measurement;
use GibsonOS\Module\Zeus\Repository\HomeRepository;
use GibsonOS\Module\Zeus\Repository\MeasurementRepository;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class InexogyMeasurementCollector
{
    private const int DIVIDER = 10000;

    public function __construct(
        private readonly InexogyClient $inexogyClient,
        private readonly HomeRepository $homeRepository,
        private readonly MeasurementRepository $measurementRepository,
        private readonly ModelWrapper $modelWrapper,
        private readonly CryptService $cryptService,
    ) {
    }

    /**
     * @throws SaveError
     * @throws ViolationException
     * @throws WebException
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function collectMeasurement(string $cryptedEmail, string $cryptedPassword): void
    {
        $email = $this->cryptService->decrypt($cryptedEmail);
        $password = $this->cryptService->decrypt($cryptedPassword);
        $consumer = new InexogyConsumer($email, $password);
        $this->inexogyClient->getConsumerToken($consumer);
        $this->inexogyClient->getRequestToken($consumer);
        $this->inexogyClient->getAuthorize($consumer);
        $this->inexogyClient->getAccessToken($consumer);

        foreach ($this->inexogyClient->getMeters($consumer) as $meter) {
            $firstMeasurementTime = $meter['firstMeasurementTime'] ?? null;
            $meterId = $meter['meterId'] ?? null;
            $measurements = [];

            if ($meterId === null) {
                continue;
            }

            try {
                $location = $meter['location'] ?? [];
                $home = $this->homeRepository->getByMeteringPointEan($meter['administrationNumber'] ?? '')
                    ->setMeteringPointId($meterId)
                    ->setStreet($location['street'] ?? '')
                    ->setStreetNumber($location['streetNumber'] ?? '')
                    ->setPostalCode($location['zip'] ?? '')
                    ->setCity($location['city'] ?? '')
                    ->setCountry($location['country'] ?? '')
                ;
                $this->modelWrapper->getModelManager()->saveWithoutChildren($home);

                try {
                    $last = $this->measurementRepository->getLatestMeasurementWithoutEnd($home);
                    $from = DateTimeImmutable::createFromInterface($last->getFrom())
                        ->modify('+1 second')
                    ;
                } catch (SelectError) {
                    $last = null;

                    if ($firstMeasurementTime === null) {
                        continue;
                    }

                    $from = new DateTimeImmutable(sprintf('@%d', $firstMeasurementTime / 1000));
                }

                $now = new DateTimeImmutable();

                while ($from < $now) {
                    $to = $from->modify('+1 day');

                    foreach ($this->inexogyClient->getReadings($consumer, $meterId, $from, $to) as $reading) {
                        $measurement = new Measurement($this->modelWrapper)
                            ->setHome($home)
                            ->setSum((int) ($reading['values']['energy'] / self::DIVIDER))
                            ->setFrom(new DateTime(sprintf('@%s', $reading['time'] / 1000)))
                        ;
                        $measurements[$measurement->getFrom()->format('Y-m-d H:i:s')] = $measurement;
                    }

                    $from = $to->modify('+1 second');
                }

                $this->setEndsAtDiffAndSave($measurements, $last);
            } catch (SelectError) {
            }
        }
    }

    /**
     * @param array<string, Measurement> $measurements
     *
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ViolationException
     */
    private function setEndsAtDiffAndSave(array $measurements, ?Measurement $last): void
    {
        ksort($measurements);

        foreach ($measurements as $measurement) {
            $measurement->setConsumption($measurement->getSum());
            $lastFrom = $last?->getFrom();
            $from = $measurement->getFrom()->setTimezone(new DateTimeZone('Europe/Berlin'));

            if ($last !== null && $lastFrom < $from) {
                $last->setTo($from);
                $this->modelWrapper->getModelManager()->saveWithoutChildren($last);
                $measurement->setConsumption($measurement->getSum() - $last->getSum());
            }

            $last = $measurement;
        }

        if ($last !== null) {
            $this->modelWrapper->getModelManager()->saveWithoutChildren($last);
        }
    }
}
