<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Form;

use GibsonOS\Core\Dto\Form;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\CryptService;

class SettingsForm
{
    public function __construct(
        private readonly CryptService $cryptService,
    ) {
    }

    public function getForm(
        ?Setting $tibberAccessToken,
        ?Setting $inexogyEmail,
        ?Setting $inexogyPassword,
    ): Form {
        return new Form(
            $this->getFields($tibberAccessToken, $inexogyEmail, $inexogyPassword),
            $this->getButtons(),
        );
    }

    /**
     * @return array<string, AbstractParameter>
     */
    private function getFields(
        ?Setting $tibberAccessTokenSetting,
        ?Setting $inexogyEmailSetting,
        ?Setting $inexogyPasswordSetting,
    ): array {
        $tibberAccessToken = $tibberAccessTokenSetting?->getValue();

        if ($tibberAccessToken !== null) {
            $tibberAccessToken = $this->cryptService->decrypt($tibberAccessToken);
        }

        $inexogyEmail = $inexogyEmailSetting?->getValue();

        if ($inexogyEmail !== null) {
            $inexogyEmail = $this->cryptService->decrypt($inexogyEmail);
        }

        $inexogyPassword = $inexogyPasswordSetting?->getValue();

        if ($inexogyPassword !== null) {
            $inexogyPassword = $this->cryptService->decrypt($inexogyPassword);
        }

        return [
            'tibberAccessToken' => (new StringParameter('Tibber Access Token'))
                ->setValue($tibberAccessToken),
            'inexogyEmail' => (new StringParameter('Ineoxgy Email'))
                ->setValue($inexogyEmail),
            'inexogyPassword' => (new StringParameter('Ineoxgy Password'))
                ->setValue($inexogyPassword),
        ];
    }

    /**
     * @return array<string, Button>
     */
    private function getButtons(): array
    {
        return [
            'save' => new Button(
                'Speichern',
                'zeus',
                'index',
                'settings',
            ),
        ];
    }
}
