<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Attribute\GetStore;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\CryptException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Service\CryptService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Zeus\Form\SettingsForm;
use GibsonOS\Module\Zeus\Model\Home;
use GibsonOS\Module\Zeus\Store\HomeStore;
use GibsonOS\Module\Zeus\Store\PriceStore;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class IndexController extends AbstractController
{
    #[CheckPermission([Permission::READ])]
    public function getHomes(
        #[GetStore]
        HomeStore $homeStore,
        #[GetSetting('tibberAccessToken', 'zeus')]
        ?Setting $tibberAccessToken,
    ): AjaxResponse {
        if ($tibberAccessToken === null) {
            return $this->returnSuccess();
        }

        return $homeStore
            ->setTibberAccessToken($tibberAccessToken->getValue())
            ->getAjaxResponse()
        ;
    }

    public function getPrices(
        #[GetStore]
        PriceStore $priceStore,
        #[GetSetting('tibberAccessToken', 'zeus')]
        ?Setting $tibberAccessToken,
        #[GetModel(['id' => 'homeId'])]
        Home $home,
    ): AjaxResponse {
        if ($home->getAccessToken() !== $tibberAccessToken?->getValue()) {
            return $this->returnSuccess();
        }

        return $priceStore
            ->setHome($home)
            ->getAjaxResponse()
        ;
    }

    #[CheckPermission([Permission::WRITE])]
    public function getForm(
        SettingsForm $settingsForm,
        #[GetSetting('tibberAccessToken', 'zeus')]
        ?Setting $tibberAccessToken,
        #[GetSetting('inexogyEmail', 'zeus')]
        ?Setting $inexogyEmail,
        #[GetSetting('inexogyPassword', 'zeus')]
        ?Setting $inexogyPassword,
        #[GetSetting('ecoflowAccessKey', 'zeus')]
        ?Setting $ecoflowAccessKey,
        #[GetSetting('ecoflowSecretKey', 'zeus')]
        ?Setting $ecoflowSecretKey,
    ): AjaxResponse {
        return $this->returnSuccess($settingsForm->getForm(
            $tibberAccessToken,
            $inexogyEmail,
            $inexogyPassword,
            $ecoflowAccessKey,
            $ecoflowSecretKey,
        ));
    }

    /**
     * @throws CryptException
     * @throws SaveError
     * @throws SelectError
     * @throws ViolationException
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    #[CheckPermission([Permission::WRITE])]
    public function postSettings(
        ModelWrapper $modelWrapper,
        ModuleRepository $moduleRepository,
        CryptService $cryptService,
        string $tibberAccessToken,
        string $inexogyEmail,
        string $inexogyPassword,
        string $ecoflowAccessKey,
        string $ecoflowSecretKey,
        #[GetSetting('tibberAccessToken', 'zeus')]
        ?Setting $tibberAccessTokenSetting,
        #[GetSetting('inexogyEmail', 'zeus')]
        ?Setting $inexogyEmailSetting,
        #[GetSetting('inexogyPassword', 'zeus')]
        ?Setting $inexogyPasswordSetting,
        #[GetSetting('ecoflowAccessKey', 'zeus')]
        ?Setting $ecoflowAccessKeySetting,
        #[GetSetting('ecoflowSecretKey', 'zeus')]
        ?Setting $ecoflowSecretKeySetting,
        User $permissionUser,
    ): AjaxResponse {
        if ($tibberAccessTokenSetting === null) {
            $tibberAccessTokenSetting = (new Setting($modelWrapper))
                ->setKey('tibberAccessToken')
                ->setModule($moduleRepository->getByName('zeus'))
                ->setUser($permissionUser)
            ;
        }

        if ($inexogyEmailSetting === null) {
            $inexogyEmailSetting = (new Setting($modelWrapper))
                ->setKey('inexogyEmail')
                ->setModule($moduleRepository->getByName('zeus'))
                ->setUser($permissionUser)
            ;
        }

        if ($inexogyPasswordSetting === null) {
            $inexogyPasswordSetting = (new Setting($modelWrapper))
                ->setKey('inexogyPassword')
                ->setModule($moduleRepository->getByName('zeus'))
                ->setUser($permissionUser)
            ;
        }

        if ($ecoflowAccessKeySetting === null) {
            $ecoflowAccessKeySetting = (new Setting($modelWrapper))
                ->setKey('ecoflowAccessKey')
                ->setModule($moduleRepository->getByName('zeus'))
                ->setUser($permissionUser)
            ;
        }

        if ($ecoflowSecretKeySetting === null) {
            $ecoflowSecretKeySetting = (new Setting($modelWrapper))
                ->setKey('ecoflowSecretKey')
                ->setModule($moduleRepository->getByName('zeus'))
                ->setUser($permissionUser)
            ;
        }

        $tibberAccessTokenSetting->setValue($cryptService->encrypt($tibberAccessToken));
        $inexogyEmailSetting->setValue($cryptService->encrypt($inexogyEmail));
        $inexogyPasswordSetting->setValue($cryptService->encrypt($inexogyPassword));
        $ecoflowAccessKeySetting->setValue($cryptService->encrypt($ecoflowAccessKey));
        $ecoflowSecretKeySetting->setValue($cryptService->encrypt($ecoflowSecretKey));

        $modelWrapper->getModelManager()->saveWithoutChildren($tibberAccessTokenSetting);
        $modelWrapper->getModelManager()->saveWithoutChildren($inexogyEmailSetting);
        $modelWrapper->getModelManager()->saveWithoutChildren($inexogyPasswordSetting);
        $modelWrapper->getModelManager()->saveWithoutChildren($ecoflowAccessKeySetting);
        $modelWrapper->getModelManager()->saveWithoutChildren($ecoflowSecretKeySetting);

        return $this->returnSuccess();
    }
}
