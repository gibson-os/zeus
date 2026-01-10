<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Store;

use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Zeus\Model\Home;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<Home>
 */
class HomeStore extends AbstractDatabaseStore
{
    private string $tibberAccessToken;

    #[Override]
    protected function getModelClassName(): string
    {
        return Home::class;
    }

    public function setTibberAccessToken(string $tibberAccessToken): HomeStore
    {
        $this->tibberAccessToken = $tibberAccessToken;

        return $this;
    }

    #[Override]
    protected function setWheres(): void
    {
        $this->addWhere('`access_token`=:accessToken', ['accessToken' => $this->tibberAccessToken]);
    }

    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`name`' => OrderDirection::ASC];
    }
}
