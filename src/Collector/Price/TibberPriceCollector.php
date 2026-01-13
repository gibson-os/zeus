<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Collector\Price;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Zeus\Client\TibberClient;
use GibsonOS\Module\Zeus\Model\Home;
use GibsonOS\Module\Zeus\Model\Price;
use GibsonOS\Module\Zeus\Repository\PriceRepository;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class TibberPriceCollector
{
    public function __construct(
        private readonly TibberClient $tibberClient,
        private readonly ModelMapper $modelMapper,
        private readonly ModelWrapper $modelWrapper,
        private readonly PriceRepository $priceRepository,
    ) {
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     * @throws WebException
     * @throws SaveError
     * @throws ViolationException
     * @throws RecordException
     * @throws ClientException
     */
    public function collectPrices(string $accessToken): void
    {
        $responseBody = $this->tibberClient->getPrices($accessToken);
        $homes = $this->collectHomes($accessToken, $responseBody['data']['viewer']['homes'] ?? []);

        /** @var array $home */
        foreach ($responseBody['data']['viewer']['homes'] ?? [] as $home) {
            $homeId = $home['id'] ?? null;

            if (!is_string($homeId)) {
                continue;
            }

            $homeModel = $homes[$homeId] ?? null;

            if ($homeModel === null) {
                continue;
            }

            $prices = $this->collectPricesByHome($home['currentSubscription']['priceInfo'] ?? [], $homeModel);
            $prices = $this->removeOldCurrentPrices($prices, $homeModel);
            $this->setEndsAtAndSave($prices, $homeModel);
        }
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ViolationException
     *
     * @return array<string, Home>
     */
    private function collectHomes(string $accessToken, array $homes): array
    {
        $homeModels = [];

        foreach ($homes as $home) {
            $homeModel = new Home($this->modelWrapper)
                ->setForeignId($home['id'])
                ->setAccessToken($accessToken)
                ->setName($home['appNickname'])
                ->setSize($home['size'])
                ->setResidents($home['numberOfResidents'])
            ;
            $this->modelWrapper->getModelManager()->saveWithoutChildren($homeModel);
            $homeModels[$home['id']] = $homeModel;
        }

        return $homeModels;
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     *
     * @return array<string, Price>
     */
    private function collectPricesByHome(array $priceInfo, Home $home): array
    {
        $prices = [];

        foreach ($priceInfo['today'] ?? [] as $price) {
            $prices[$price['startsAt']] = $this->modelMapper->mapToObject(Price::class, $price)
                ->setHome($home);
        }

        foreach ($priceInfo['tomorrow'] ?? [] as $price) {
            $prices[$price['startsAt']] = $this->modelMapper->mapToObject(Price::class, $price)
                ->setHome($home);
        }

        $prices[$priceInfo['current']['startsAt']] = $this->modelMapper->mapToObject(Price::class, $priceInfo['current'])
            ->setCurrent(true)
            ->setHome($home);

        return $prices;
    }

    /**
     * @param array<string, Price> $prices
     *
     * @return array<string, Price>
     */
    private function removeOldCurrentPrices(array $prices, Home $home): array
    {
        $currentPrices = array_flip(array_map(
            static fn (Price $price): string => $price->getStartsAt()->format('Y-m-d H:i:s'),
            $this->priceRepository->getCurrentsByStartDates(
                $home,
                array_values(array_map(
                    static fn (Price $price): string => $price->getStartsAt()->format('Y-m-d H:i:s'),
                    $prices,
                )),
            ),
        ));

        return array_filter(
            $prices,
            static fn (Price $price): bool => $price->isCurrent() || !isset($currentPrices[$price->getStartsAt()->format('Y-m-d H:i:s')]),
        );
    }

    /**
     * @param array<string, Price> $prices
     *
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ViolationException
     * @throws ClientException
     */
    private function setEndsAtAndSave(array $prices, Home $home): void
    {
        try {
            $last = $this->priceRepository->getLatestPriceWithoutEnd($home);
        } catch (SelectError) {
            $last = null;
        }

        ksort($prices);

        foreach ($prices as $price) {
            if ($last !== null && $last->getStartsAt() < $price->getStartsAt()) {
                $last->setEndsAt($price->getStartsAt());
                $this->modelWrapper->getModelManager()->saveWithoutChildren($last);
            }

            $last = $price;
        }
    }
}
