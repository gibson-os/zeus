<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use Override;

class TibberInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    #[Override]
    public function install(string $module): Generator
    {
        yield $tibberAccessTokenInput = $this->getEnvInput(
            'TIBBER_ACCESS_TOKEN',
            'If you use Tibber enter the access token here. Leave empty if not.',
        );

        $tibberAccessToken = $tibberAccessTokenInput->getValue() ?? '';

        if (mb_substr($tibberAccessToken, -1) !== '/') {
            $tibberAccessToken .= '/';
        }

        yield (new Configuration('Tibber configuration generated!'))
            ->setValue('TIBBER_ACCESS_TOKEN', $tibberAccessToken)
        ;
    }

    #[Override]
    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    #[Override]
    public function getPriority(): int
    {
        return 800;
    }
}
