<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use ReflectionException;

class AppData extends AbstractInstall implements PriorityInterface
{
    /**
     * @throws ClientException
     * @throws RecordException
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[Override]
    public function install(string $module): Generator
    {
        $this->addApp('Zeus', 'zeus', 'index', 'index', 'icon_exe');

        yield new Success('Zeus apps installed!');
    }

    #[Override]
    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    #[Override]
    public function getModule(): ?string
    {
        return 'zeus';
    }

    #[Override]
    public function getPriority(): int
    {
        return 0;
    }
}
