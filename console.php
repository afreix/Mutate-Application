<?php
$app = require "bootstrap.php";

$console = new Slimfra\Console($app);

//CLI commands go here 
$console->add(new Command\SetupDatabase());

$console->run();