<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Repository\Device;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Zeus\Model\Device;
use GibsonOS\Module\Zeus\Model\Device\Measurement;
use JsonException;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class MeasurementRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getLastByDevice(Device $device): Measurement
    {
        return $this->fetchOne(
            '`device_id`=:deviceId',
            ['deviceId' => $device->getId() ?? 0],
            Measurement::class,
            ['`from`' => OrderDirection::DESC],
        );
    }
}
