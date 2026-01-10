<?php
declare(strict_types=1);

namespace Command;

use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use Override;
use Psr\Log\LoggerInterface;
use TibberProvider;

#[Lock('zeusCollectPrices')]
#[Cronjob(minutes: '0,5,10,15,20,25,30,35,40,45,50,55', seconds: '21')]
class CollectPricesCommand extends AbstractCommand
{
    public function __construct(
        private readonly TibberProvider $tibberProvider,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    #[Override]
    protected function run(): int
    {
        return self::SUCCESS;
    }
}
