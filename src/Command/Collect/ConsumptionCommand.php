<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Command\Collect;

use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Module\Zeus\Collector\Measurement\InexogyMeasurementCollector;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use Psr\Log\LoggerInterface;
use ReflectionException;

#[Lock('zeusCollectConsumption')]
#[Cronjob(minutes: '1,16,31,46', seconds: '42')]
class ConsumptionCommand extends AbstractCommand
{
    public function __construct(
        private readonly InexogyMeasurementCollector $inexogyMeasurementCollector,
        private readonly SettingRepository $settingRepository,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
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
    #[Override]
    protected function run(): int
    {
        $emailSettings = $this->settingRepository->getAllByKeyAndModuleName('zeus', 'inexogyEmail');
        $passwordSettings = $this->settingRepository->getAllByKeyAndModuleName('zeus', 'inexogyPassword');
        $usedEmails = [];

        foreach ($emailSettings as $emailSetting) {
            $email = $emailSetting->getValue();
            if (in_array($email, $usedEmails)) {
                continue;
            }

            $password = array_find(
                $passwordSettings,
                fn (Setting $passwordSetting): bool => $passwordSetting->getUserId() === $emailSetting->getUserId(),
            )?->getValue();

            if ($password === null) {
                continue;
            }

            $this->inexogyMeasurementCollector->collectMeasurement($email, $password);
            $usedEmails[] = $email;
        }

        return self::SUCCESS;
    }
}
