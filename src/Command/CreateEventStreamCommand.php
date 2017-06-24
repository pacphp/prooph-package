<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Command;

use ArrayIterator;
use Pac\Console\Command;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEventStreamCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('event-store:create-event-stream')
            ->setDescription('Sets up an event stream in the database')
            ->addArgument('event-stream', InputArgument::REQUIRED, 'The name of the event store');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventStream = strtolower($input->getArgument('event-stream'));

        $eventStore = $this->getContainer()->get('prooph_event_store.' . $eventStream);

        $eventStore->create(new Stream(new StreamName($eventStream), new ArrayIterator()));
    }
}
