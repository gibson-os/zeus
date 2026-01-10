<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Form;

use GibsonOS\Core\Dto\Form;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Model\Setting;

class SettingsForm
{
    public function __construct()
    {
    }

    public function getForm(?Setting $setting): Form
    {
        return new Form(
            $this->getFields($setting),
            $this->getButtons($setting),
        );
    }

    /**
     * @return array<string, AbstractParameter>
     */
    private function getFields(?Setting $setting): array
    {
        return [
            'tibberAccessToken' => (new StringParameter('Tibber Access Token'))
                ->setValue($setting?->getValue()),
        ];
    }

    /**
     * @return array<string, Button>
     */
    private function getButtons(?Setting $setting): array
    {
        $parameters = [];
        $id = $setting?->getId();

        if ($id !== null) {
            $parameters['id'] = $id;
        }

        return [
            'save' => new Button(
                'Speichern',
                'zeus',
                'index',
                'settings',
                $parameters,
            ),
        ];
    }
}
