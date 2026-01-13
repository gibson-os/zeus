<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Command;

use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Module\Zeus\Collector\Price\TibberPriceCollector;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * @description Collect prices from Tibber API
 */
#[Lock('zeusCollectPrices')]
#[Cronjob(minutes: '1,16,31,46', seconds: '21')]
class CollectPricesCommand extends AbstractCommand
{
    public function __construct(
        private readonly TibberPriceCollector $tibberPriceCollector,
        private readonly SettingRepository $settingRepository,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws FactoryError
     * @throws MapperException
     * @throws SaveError
     * @throws ViolationException
     * @throws WebException
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    #[Override]
    protected function run(): int
    {
        $accessTokens = array_unique(array_map(
            static fn (Setting $setting): string => $setting->getValue(),
            $this->settingRepository->getAllByKeyAndModuleName('zeus', 'tibberAccessToken'),
        ));

        foreach ($accessTokens as $accessToken) {
            $this->tibberPriceCollector->collectPrices($accessToken);
        }

        return self::SUCCESS;
    }
}
