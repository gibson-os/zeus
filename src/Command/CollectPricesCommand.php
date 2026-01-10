<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Command;

use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Module\Zeus\Model\Price;
use GibsonOS\Module\Zeus\Provider\TibberProvider;
use GibsonOS\Module\Zeus\Repository\PriceRepository;
use Override;
use Psr\Log\LoggerInterface;

/**
 * @description Collect prices from Tibber API
 */
#[Lock('zeusCollectPrices')]
#[Cronjob(minutes: '1,16,31,46', seconds: '21')]
class CollectPricesCommand extends AbstractCommand
{
    public function __construct(
        private readonly TibberProvider $tibberProvider,
        private readonly ModelManager $modelManager,
        private readonly PriceRepository $priceRepository,
        private readonly SettingRepository $settingRepository,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    #[Override]
    protected function run(): int
    {
        $accessTokens = array_unique(array_map(
            static fn (Setting $setting): string => $setting->getValue(),
            $this->settingRepository->getAllByKeyAndModuleName('zeus', 'tibberAccessToken'),
        ));

        foreach ($accessTokens as $accessToken) {
            $pricesByHome = [];

            foreach ($this->tibberProvider->getPrices($accessToken) as $price) {
                $pricesByHome[$price->getHomeId()][] = $price;
            }

            foreach ($pricesByHome as $homeId => $prices) {
                $currentPrices = array_flip(array_map(
                    static fn (Price $price): string => $price->getStartsAt()->format('Y-m-d H:i:s'),
                    $this->priceRepository->getCurrentsByStartDates(
                        $homeId,
                        array_map(
                            static fn (Price $price): string => $price->getStartsAt()->format('Y-m-d H:i:s'),
                            $prices,
                        ),
                    ),
                ));

                foreach ($prices as $price) {
                    if (
                        !$price->isCurrent()
                        && isset($currentPrices[$price->getStartsAt()->format('Y-m-d H:i:s')])
                    ) {
                        continue;
                    }

                    $this->logger->debug(sprintf(
                        'Saving price %s for home #%d',
                        $price->getStartsAt()->format('Y-m-d H:i:s'),
                        $price->getHomeId(),
                    ));

                    $this->modelManager->saveWithoutChildren($price);
                }
            }
        }

        return self::SUCCESS;
    }
}
