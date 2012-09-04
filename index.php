<?php
//this is the autoload file created by 'composer' when you install dependencies,
//it ensures that code for any dependency libraries is loaded automatically when it's
//called the first time
use Symfony\Component\HttpFoundation\StreamedResponse;

$app = require "bootstrap.php";

/******* Test paths **********/
//Shows the contents of the presets table
$app->get('/testPreset',"AndrewTest1::testPreset");	
//Delets all the contents of presets
$app->get('/deletePreset',"AndrewTest1::deletePreset");
//Shows the contents of the transcodes table
$app->get('/test',"AndrewTest1::test");	
//Deletes all the contents of transcodes
$app->get('/deleteTest',"AndrewTest1::delete");
//Inserts fake test data into the presets table
$app->get('/insertTest',"AndrewTest1::insertTest");
//Allows you to test a preset on an input file seperate from the UI (go to the TranscodeController to change the input/output file)
$app->get("/preset/{preset_key}", "TranscodeController::testPreset");
/******* End Test paths **********/

//Generates the beggining page
$app->get('/',"InputOutputController::display");

/***** The following three paths are all part of the transcode process *****/
//Schedules a transcoding job
$app->match('/schedule', "MutateController::scheduleJob");
//Pulls information from the database for a scheduled job and transcodes the input file
$app->match('/transcode',"MutateController::transcodeUsingJob");
//Updates the database after completion of the transcode process and fills the popover content of the output files
$app->match('/update_retrieve',"MutateController::updateAndRetrieve");

//Inserts data into the database
$app->match('/insert_data', "DatabaseController::insertIntoSqlite");
//Manages the process of uploading files
$app->match('/upload', "FileController::uploadFile");
//Manages the process of deleting files (on the screen and in the database if an output file)
$app->match('/delete', "FileController::deleteFile");

//Inserts new preset information into the database
$app->match('/createPreset',"MutateController::addPreset");

$app->run();
