<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Zeus\Model\Home;
use GibsonOS\Module\Zeus\Model\Measurement;
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
    public function getLatestMeasurementWithoutEnd(Home $home): Measurement
    {
        return $this->fetchOne(
            '`home_id`=:homeId AND `to` IS NULL',
            ['homeId' => $home->getId() ?? 0],
            Measurement::class,
            orderBy: ['`from`' => OrderDirection::DESC],
        );
    }
}
