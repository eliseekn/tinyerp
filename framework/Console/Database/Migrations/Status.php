<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

namespace Framework\Console\Database\Migrations;

use Framework\System\Storage;
use Framework\Database\QueryBuilder;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Display migrations tables status
 */
class Status extends Command
{
    protected static $defaultName = 'db:migrations:status';

    protected function configure()
    {
        $this->setDescription('Display migrations tables status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rows = [];

        foreach (Storage::path(config('storage.migrations'))->getFiles() as $table) {
            $status = $this->isMigrated(get_file_name($table)) ? '<info>migrated</info>' : '<fg=red>not migrated</info>';
            $rows[] = [get_file_name($table), $status];
        }

        $table = new Table($output);
        $table->setHeaders(['Tables', 'Status']);
        $table->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }
    
    protected function isMigrated(string $table): bool
    {
        if (!QueryBuilder::tableExists('migrations')) {
            return false;
        }

        return QueryBuilder::table('migrations')
            ->select('*')
            ->where('name', $table)
            ->exists();
    }
    
}
