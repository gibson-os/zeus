<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Repository;

use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Zeus\Model\Price;

class PriceRepository extends AbstractRepository
{
    public function getCurrentsByStartDates(int $homeId, array $startsAts): array
    {
        $parameters = $startsAts;
        $parameters['homeId'] = $homeId;
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
}
