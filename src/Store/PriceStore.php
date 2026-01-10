<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Store;

use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Zeus\Model\Home;
use GibsonOS\Module\Zeus\Model\Price;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<Price>
 */
class PriceStore extends AbstractDatabaseStore
{
    private Home $home;

    #[Override]
    protected function getModelClassName(): string
    {
        return Price::class;
    }

    public function setHome(Home $home): PriceStore
    {
        $this->home = $home;

        return $this;
    }

    #[Override]
    protected function setWheres(): void
    {
        $this->addWhere('`home_id`=:homeId', ['homeId' => $this->home->getId() ?? 0]);
    }

    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`starts_at`' => OrderDirection::DESC];
    }
}
