<?php
namespace App\Console\Command\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use App\Models\Migration;

class Apply extends Command
{
    private $http;

    protected function configure()
    {
        $this->setName('migrations:apply')
            ->setDescription("Apply migrations.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $files = glob('database/migrations/*.php');
        $batch = time();
        $applied = 0;

        foreach ($files as $file)
        {
            $class = basename($file, '.php');

            if (!Migration::migrated($class)->get()->count())
            {
                $output->writeln("Going to apply $class");

                require_once($file);
                
                $migration = new $class;
                $migration->up();

                $migrationRow = new Migration([
                    'name'  => $class,
                    'batch' => $batch,
                ]);

                $migrationRow->save();

                $applied++;
            }
        }

        $output->writeln("$applied migrations where applied.");
    }
}
