<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Collector\Measurement;

use DateTimeImmutable;
use GibsonOS\Core\Service\CryptService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Zeus\Client\EcoflowClient;
use GibsonOS\Module\Zeus\Enum\QuotaCode;
use GibsonOS\Module\Zeus\Model\Device;

class EcoflowMeasurementCollector
{
    public function __construct(
        private readonly EcoflowClient $ecoflowClient,
        private readonly CryptService $cryptService,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    public function collectMeasurement(string $accessKey, string $secretKey): void
    {
        $accessKey = $this->cryptService->decrypt($accessKey);
        $secretKey = $this->cryptService->decrypt($secretKey);

        $devices = $this->ecoflowClient->getDeviceList($accessKey, $secretKey);

        foreach ($devices as $device) {
            $deviceModel = new Device($this->modelWrapper)
                ->setAccessKey($accessKey)
                ->setSerialNumber($device['sn'])
                ->setDeviceName($device['deviceName'])
                ->setOnline((bool) $device['online']);

            $this->modelWrapper->getModelManager()->saveWithoutChildren($deviceModel);

            //            var_dump($this->ecoflowClient->getDeviceQuota(
            //                $accessKey,
            //                $secretKey,
            //                $deviceModel,
            //                QuotaCode::SOLAR_ENERGY,
            //                new DateTimeImmutable('2026-01-01 00:00:00'),
            //                new DateTimeImmutable('2026-01-01 23:59:59'),
            //            ));
        }
    }
}
