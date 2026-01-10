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
use GibsonOS\Core\Repository\ModuleRepository;
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
    ): AjaxResponse {
        return $this->returnSuccess($settingsForm->getForm($tibberAccessToken));
    }

    #[CheckPermission([Permission::WRITE])]
    public function postSettings(
        ModelWrapper $modelWrapper,
        ModuleRepository $moduleRepository,
        string $tibberAccessToken,
        #[GetSetting('tibberAccessToken', 'zeus')]
        ?Setting $tibberAccessTokenSetting,
    ): AjaxResponse {
        if ($tibberAccessTokenSetting === null) {
            $tibberAccessTokenSetting = (new Setting($modelWrapper))
                ->setKey('systemModel')
                ->setModule($moduleRepository->getByName('marvin'))
            ;
        }

        $tibberAccessTokenSetting->setValue($tibberAccessToken);
        $modelWrapper->getModelManager()->saveWithoutChildren($tibberAccessTokenSetting);

        return $this->returnSuccess();
    }
}
