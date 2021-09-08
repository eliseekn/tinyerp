<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

namespace Framework\Console\Database\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reset migrations tables
 */
class Reset extends Command
{
    protected static $defaultName = 'db:migrations:reset';

    protected function configure()
    {
        $this->setDescription('Reset migrations tables');
        $this->addArgument('table', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'The name of migrations tables (separated by space if many)');
        $this->addOption('seed', null, InputOption::VALUE_OPTIONAL, 'Insert all seeds');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tables = $input->getArgument('table');

        $this->getApplication()->find('db:migrations:delete')->run(new ArrayInput($tables), $output);
        $this->getApplication()->find('db:migrations:run')->run(new ArrayInput($tables), $output);

        if ($input->hasOption('seed')) {
            $this->getApplication()->find('db:seed')->run(new ArrayInput($tables), $output);
        }

        return Command::SUCCESS;
    }
}
