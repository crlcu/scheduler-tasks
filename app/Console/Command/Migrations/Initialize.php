<?php
namespace App\Console\Command\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use App\Models\Migration;

class Initialize extends Command
{
    private $http;

    protected function configure()
    {
        $this->setName('migrations:init')
            ->setDescription("Initialize migrations.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = glob('database/migrations/CreateMigrationsTable.php');
        
        foreach ($files as $file)
        {
            $class = basename($file, '.php');

            $output->writeln("Going to initialize migrations.");

            require_once($file);
            
            $migration = new $class;
            $migration->up();
        }

        $migration = new Migration([
            'name'  => 'CreateMigrationsTable',
            'batch' => time(),
        ]);

        $migration->save();
    }
}
