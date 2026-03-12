<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Collector\Measurement;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\CryptService;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Zeus\Client\EcoflowClient;
use GibsonOS\Module\Zeus\Enum\QuotaCode;
use GibsonOS\Module\Zeus\Exception\CollectException;
use GibsonOS\Module\Zeus\Model\Device;
use GibsonOS\Module\Zeus\Model\Device\Measurement;
use GibsonOS\Module\Zeus\Repository\Device\MeasurementRepository;

class EcoflowMeasurementCollector
{
    public function __construct(
        private readonly EcoflowClient $ecoflowClient,
        private readonly CryptService $cryptService,
        private readonly ModelWrapper $modelWrapper,
        private readonly MeasurementRepository $measurementRepository,
        private readonly DateTimeService $dateTimeService,
    ) {
    }

    public function collectMeasurements(string $cryptedAccessKey, string $cryptedSecretKey): void
    {
        $accessKey = $this->cryptService->decrypt($cryptedAccessKey);
        $secretKey = $this->cryptService->decrypt($cryptedSecretKey);

        $devices = $this->ecoflowClient->getDeviceList($accessKey, $secretKey);

        foreach ($devices as $device) {
            $from = new DateTimeImmutable('2026-03-11 12:00:00');
            $to = new DateTimeImmutable('2026-03-11 12:59:59');

            $deviceModel = new Device($this->modelWrapper)
                ->setAccessKey($accessKey)
                ->setSerialNumber($device['sn'])
                ->setDeviceName($device['deviceName'])
                ->setOnline((bool) $device['online']);

            $this->modelWrapper->getModelManager()->saveWithoutChildren($deviceModel);

            try {
                $lastMeasurement = $this->measurementRepository->getLastByDevice($deviceModel);
                $from = $lastMeasurement->getFrom()->modify('+1 hour');
                $to = $lastMeasurement->getTo()->modify('+1 hour');
                $now = $this->dateTimeService->get();

                while ($to < $now) {
                    try {
                        $this->collectMeasurement($accessKey, $secretKey, $deviceModel, $from, $to);
                    } catch (CollectException) {
                        continue 2;
                    }

                    $from->modify('+1 hour');
                    $to->modify('+1 hour');
                }
            } catch (SelectError) {
                $from = $this->dateTimeService->get();
                $to = $this->dateTimeService->get();
                $from->modify('-1 hour')->setTime((int) $from->format('H'), 0, 0);
                $to->modify('-1 hour')->setTime((int) $to->format('H'), 59, 59);

                while (true) {
                    try {
                        $this->collectMeasurement($accessKey, $secretKey, $deviceModel, $from, $to);
                    } catch (CollectException) {
                        continue 2;
                    }

                    $from->modify('-1 hour');
                    $to->modify('-1 hour');
                }
                // Revert collect bis keine Daten mehr kommen
            }
        }
    }

    /**
     * @throws CollectException
     */
    private function collectMeasurement(
        string $accessKey,
        string $secretKey,
        Device $device,
        DateTimeInterface $from,
        DateTimeInterface $to,
    ): void {
        $measurement = new Measurement($this->modelWrapper)
            ->setDevice($device)
            ->setFrom(DateTime::createFromInterface($from))
            ->setTo(DateTime::createFromInterface($to))
        ;

        $this->collectDeviceEnergy($accessKey, $secretKey, $device, $from, $to, $measurement);
        $this->collectGridEnergy($accessKey, $secretKey, $device, $from, $to, $measurement);
        $this->collectSocEnergy($accessKey, $secretKey, $device, $from, $to, $measurement);

        $this->modelWrapper->getModelManager()->saveWithoutChildren($measurement);
    }

    /**
     * @throws CollectException
     */
    private function collectDeviceEnergy(
        string $accessKey,
        string $secretKey,
        Device $device,
        DateTimeInterface $from,
        DateTimeInterface $to,
        Measurement $measurement,
    ): void {
        $loadEnergy = $this->ecoflowClient->getDeviceQuota(
            $accessKey,
            $secretKey,
            $device,
            QuotaCode::LOAD_ENERGY,
            $from,
            $to,
        );

        if (!is_array($loadEnergy['data']['data'] ?? null)) {
            throw new CollectException('No device energy data found');
        }

        $measurement->setDeviceConsumption((int) ($loadEnergy['data']['data'][0]['indexValue'] ?? 0));
    }

    /**
     * @throws CollectException
     */
    private function collectGridEnergy(
        string $accessKey,
        string $secretKey,
        Device $device,
        DateTimeInterface $from,
        DateTimeInterface $to,
        Measurement $measurement,
    ): void {
        $gridEnergy = $this->ecoflowClient->getDeviceQuota(
            $accessKey,
            $secretKey,
            $device,
            QuotaCode::GRID_ENERGY,
            $from,
            $to,
        );

        if (!is_array($gridEnergy['data']['data'] ?? null)) {
            throw new CollectException('No grid energy data found');
        }

        $gridConsumption = 0;
        $gridFeedIn = 0;

        foreach ($gridEnergy['data']['data'] as $gridEnergyData) {
            switch ($gridEnergyData['extra']) {
                case '2':
                    $gridFeedIn = (int) $gridEnergyData['indexValue'];

                    break;
                default:
                    $gridConsumption = (int) $gridEnergyData['indexValue'];

                    break;
            }
        }

        $measurement
            ->setGridConsumption($gridConsumption)
            ->setGridFeedIn($gridFeedIn)
        ;
    }

    /**
     * @throws CollectException
     */
    private function collectSocEnergy(
        string $accessKey,
        string $secretKey,
        Device $device,
        DateTimeInterface $from,
        DateTimeInterface $to,
        Measurement $measurement,
    ): void {
        $socEnergy = $this->ecoflowClient->getDeviceQuota(
            $accessKey,
            $secretKey,
            $device,
            QuotaCode::SOC_ENERGY,
            $from,
            $to,
        );

        if (!is_array($socEnergy['data']['data'] ?? null)) {
            throw new CollectException('No soc energy data found');
        }

        $socConsumption = 0;
        $socFeedIn = 0;

        foreach ($socEnergy['data']['data'] as $socEnergyData) {
            switch ($socEnergyData['extra']) {
                case '2':
                    $socFeedIn = (int) $socEnergyData['indexValue'];

                    break;
                default:
                    $socConsumption = (int) $socEnergyData['indexValue'];

                    break;
            }
        }

        $measurement
            ->setBatteryConsumption($socConsumption)
            ->setBatteryFeedIn($socFeedIn)
        ;
    }
}
