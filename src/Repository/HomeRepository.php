<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use GibsonOS\Module\Zeus\Model\Home;
use JsonException;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class HomeRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getById(string $accessToken, int $id): Home
    {
        return $this->fetchOne(
            '`id`=:id AND `access_token`=:accessToken',
            [
                'id' => $id,
                'accessToken' => $accessToken,
            ],
            Home::class,
        );
    }

    /**
     * @throws ClientException
     * @throws RecordException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function findByName(string $accessToken, string $name): array
    {
        return $this->fetchAll(
            '`name` LIKE :name AND `access_token`=:accessToken',
            [
                'name' => $name . '%',
                'accessToken' => $accessToken,
            ],
            Home::class,
            orderBy: ['`name`' => OrderDirection::ASC],
        );
    }
}
