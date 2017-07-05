<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Command;

use ArrayIterator;
use Pac\Console\Command;
use Pac\ProophPackage\DependencyInjection\ProjectionLoader;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectionResetStorageCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('projection:reset-storage')
            ->setDescription('Clear the data from the projection persistence')
            ->addArgument('projection', InputArgument::REQUIRED, 'The name of the event store');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectionName = strtolower($input->getArgument('projection'));

        /** @var ReadModel $readModel */
        $readModel = $this->getContainer()->get(ProjectionLoader::readModelId($projectionName));

        $readModel->reset();
    }
}
