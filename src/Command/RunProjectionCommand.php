<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Command;

use Pac\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunProjectionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('prooph:run-projection')
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
{
    // outputs multiple lines to the console (adding "\n" at the end of each line)
    $output->writeln([
        'User Creator',
        '============',
        '',
    ]);

    // outputs a message followed by a "\n"
    $output->writeln('Whoa!');

    // outputs a message without adding a "\n" at the end of the line
    $output->write('You are about to ');
    $output->write('create a user.');
}
}
