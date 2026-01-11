<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\AutoComplete;

use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Module\Zeus\Model\Home;
use GibsonOS\Module\Zeus\Repository\HomeRepository;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use ReflectionException;

class HomeAutoComplete implements AutoCompleteInterface
{
    public function __construct(
        private readonly HomeRepository $homeRepository,
        #[GetSetting('tibberAccessToken', 'zeus')]
        private readonly ?Setting $tibberAccessTokenSetting,
    ) {
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     */
    #[Override]
    public function getByNamePart(string $namePart, array $parameters): array
    {
        $accessToken = $this->tibberAccessTokenSetting?->getValue();

        if ($accessToken === null) {
            return [];
        }

        return $this->homeRepository->findByName($accessToken, $namePart);
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    #[Override]
    public function getById(string $id, array $parameters): Home
    {
        $accessToken = $this->tibberAccessTokenSetting?->getValue();

        if ($accessToken === null) {
            throw new SelectError('No access token set');
        }

        return $this->homeRepository->getById($accessToken, (int) $id);
    }

    #[Override]
    public function getModel(): string
    {
        return 'GibsonOS.module.zeus.model.Home';
    }

    #[Override]
    public function getValueField(): string
    {
        return 'id';
    }

    #[Override]
    public function getDisplayField(): string
    {
        return 'name';
    }
}
