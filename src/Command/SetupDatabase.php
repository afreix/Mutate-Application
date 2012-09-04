<?php

namespace Command;

use Slimfra\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is an console command registered from `console` that sets up a sqlite database.
 * For more on how to use the console component, see the Symfony documentation
 * here: http://symfony.com/doc/current/components/console.html
 */
class SetupDatabase extends Command
{
	protected function configure()
    {
        $this->setName("db:setup")
            ->setDescription("Initialize the database schema for storing transcode data.");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //get the db, force connection immediately to test
        $db = $this->app['db'];
        $db->connect();
        
        //check if db is already initialized        
        try {
            if ($results = $db->query("SELECT * FROM `transcodes`")) {
                $output->writeln("Database already initialized.");
                return;
            }
        } catch(\Exception $e) {
            
        }
        
        //create sql schema
        $schema = new \Doctrine\DBAL\Schema\Schema();
        $myTable = $schema->createTable("transcodes");
		$myTable->addColumn("id", "integer", array("unsigned" => true));
        $myTable->addColumn("infile", "string");
		$myTable->addColumn("preset_key", "string");
		$myTable->addColumn("outfile", "string");
		$myTable->addColumn("status", "string");
        $myTable->addColumn("result", "text");
		$myTable->setPrimaryKey(array("id"));
		$myTable->getColumn("id")->setAutoincrement(true);
		$myTable1 = $schema->createTable("presets");
		$myTable1->addColumn("key", "string");
		$myTable1->addColumn("adapter", "string");
		$myTable1->addColumn("options", "text");
        $queries = $schema->toSql($db->getDatabasePlatform());
        
        //execute queries to setup db
        foreach ($queries as $query) {
            $db->exec($query);
        }
        
        $output->writeln("Database setup.");
    }
}