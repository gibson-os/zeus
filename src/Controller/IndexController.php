<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Attribute\GetStore;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
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
    ): AjaxResponse {
        return $this->returnSuccess($settingsForm->getForm(
            $tibberAccessToken,
            $inexogyEmail,
            $inexogyPassword,
        ));
    }

    #[CheckPermission([Permission::WRITE])]
    public function postSettings(
        ModelWrapper $modelWrapper,
        ModuleRepository $moduleRepository,
        CryptService $cryptService,
        string $tibberAccessToken,
        string $inexogyEmail,
        string $inexogyPassword,
        #[GetSetting('tibberAccessToken', 'zeus')]
        ?Setting $tibberAccessTokenSetting,
        #[GetSetting('inexogyEmail', 'zeus')]
        ?Setting $inexogyEmailSetting,
        #[GetSetting('inexogyPassword', 'zeus')]
        ?Setting $inexogyPasswordSetting,
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

        $tibberAccessTokenSetting->setValue($cryptService->encrypt($tibberAccessToken));
        $inexogyEmailSetting->setValue($cryptService->encrypt($inexogyEmail));
        $inexogyPasswordSetting->setValue($cryptService->encrypt($inexogyPassword));

        $modelWrapper->getModelManager()->saveWithoutChildren($tibberAccessTokenSetting);
        $modelWrapper->getModelManager()->saveWithoutChildren($inexogyEmailSetting);
        $modelWrapper->getModelManager()->saveWithoutChildren($inexogyPasswordSetting);

        return $this->returnSuccess();
    }
}
