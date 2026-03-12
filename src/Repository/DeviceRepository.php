<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Zeus\Model\Device;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class DeviceRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getBySerialNumber(string $serialNumber, string $accessKey): Device
    {
        return $this->fetchOne(
            '`serial_number`=:serialNumber AND `access_key`=:accessKey',
            ['serialNumber' => $serialNumber, 'accessKey' => $accessKey],
            Device::class,
        );
    }
}
