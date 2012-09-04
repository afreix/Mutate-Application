<?php
//this is the autoload file created by 'composer' when you install dependencies,
//it ensures that code for any dependency libraries is loaded automatically when it's
//called the first time
require 'vendor/autoload.php';

set_time_limit(0);
error_reporting(E_ALL);
ini_set ('display_errors', 'on');

$app = new Slimfra\Application(array(
	'debug' => true,
	'app.service.db' => true,
	'app.name' => 'Mutate Application',
	'cur_dir' => getcwd(),
	'app_name' => 'Transcoding',
	//Max file size for uploaded files
	'max_file_size' => '100000000',
));

return $app;