<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Command\Collect;

use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Module\Zeus\Collector\Measurement\EcoflowMeasurementCollector;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use Psr\Log\LoggerInterface;
use ReflectionException;

#[Lock('zeusCollectDevices')]
#[Cronjob(minutes: '1,16,31,46', seconds: '0')]
class DevicesCommand extends AbstractCommand
{
    public function __construct(
        private readonly EcoflowMeasurementCollector $ecoflowMeasurementCollector,
        private readonly SettingRepository $settingRepository,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     */
    #[Override]
    protected function run(): int
    {
        $accessKeySettings = $this->settingRepository->getAllByKeyAndModuleName('zeus', 'ecoflowAccessKey');
        $secretKeySettings = $this->settingRepository->getAllByKeyAndModuleName('zeus', 'ecoflowSecretKey');
        $usedAccessKeys = [];

        foreach ($accessKeySettings as $accessKeySetting) {
            $accessKey = $accessKeySetting->getValue();

            if (in_array($accessKey, $usedAccessKeys)) {
                continue;
            }

            $secretKey = array_find(
                $secretKeySettings,
                fn (Setting $secretKeySetting): bool => $secretKeySetting->getUserId() === $accessKeySetting->getUserId(),
            )?->getValue();

            if ($secretKey === null) {
                continue;
            }

            $this->ecoflowMeasurementCollector->collectMeasurements($accessKey, $secretKey);
            $usedAccessKeys[] = $accessKey;
        }

        return self::SUCCESS;
    }
}
