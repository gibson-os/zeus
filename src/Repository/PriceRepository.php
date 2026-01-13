<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Repository;

use DateTimeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Zeus\Model\Home;
use GibsonOS\Module\Zeus\Model\Price;
use JsonException;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class PriceRepository extends AbstractRepository
{
    public function getCurrentsByStartDates(Home $home, array $startsAts): array
    {
        $parameters = $startsAts;
        $parameters['homeId'] = $home->getId() ?? 0;
        $parameters['current'] = 1;

        return $this->fetchAll(
            sprintf(
                '`starts_at` IN (%s) AND `home_id`=:homeId AND `current`=:current',
                $this->getRepositoryWrapper()->getSelectService()->getParametersString($startsAts),
            ),
            $parameters,
            Price::class,
        );
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getLatestPriceWithoutEnd(Home $home): Price
    {
        return $this->fetchOne(
            '`home_id`=:homeId AND `ends_at` IS NULL',
            ['homeId' => $home->getId() ?? 0],
            Price::class,
            orderBy: ['starts_at' => OrderDirection::DESC],
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Price[]
     */
    public function getBestPrices(
        int $homeId,
        DateTimeInterface $from,
        DateTimeInterface $to,
        int $limit = 1,
    ): array {
        return $this->fetchAll(
            '`home_id`=:homeId AND ' .
            '(`starts_at` BETWEEN :from AND :to OR IFNULL(`ends_at`, NOW()) BETWEEN :from AND :to)',
            [
                'homeId' => $homeId,
                'from' => $from->format('Y-m-d H:i:s'),
                'to' => $to->format('Y-m-d H:i:s'),
            ],
            Price::class,
            $limit,
            orderBy: ['price' => OrderDirection::ASC],
        );
    }
}
