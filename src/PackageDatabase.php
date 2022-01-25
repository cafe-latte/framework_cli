<?php

namespace Cafelatte\PackageManager;


class PackageDatabase
{

    public function __construct()
    {

    }

    /**
     * @param $dbHost
     * @param $dbName
     * @param $dbUser
     * @param $dbPass
     */
    public function doUpdate($dbHost, $dbName, $dbUser, $dbPass)
    {
        $this->doValidationConnect($dbHost, $dbName, $dbUser, $dbPass);
        $this->doValidationPropelInstall();

        $dbStr = mb_convert_case($dbName, MB_CASE_TITLE, "UTF-8");
        $dbPascalName = str_replace(array('-', '_', ' ', '!', '#', '$', '%', '^', '&', '*', '[', ']', '~', '{', '}', ':'), "", $dbStr);


        ConsoleLog::doPrintMessage("black", "white", "step (1/6) \t: propel permission changed.", 1);
        Console::doCommandExec('chmod 777 ./vendor/propel/propel/bin/propel');
        sleep(1);

        ConsoleLog::doPrintMessage("black", "white", "step (2/6) \t: generate reverse database.", 1);
        Console::doCommandExec("./vendor/propel/propel/bin/propel reverse --database-name {$dbName} \"mysql:host={$dbHost};dbname={$dbName};user={$dbUser};password={$dbPass}\"");
        sleep(1);

        ConsoleLog::doPrintMessage("black", "white", "step (3/6) \t: change namespace.", 1);
        $oriFileName = "./generated-reversed-database/schema.xml";
        $newFileName = "./generated-reversed-database/new.xml";
        $handle = fopen($oriFileName, "r");
        if ($handle) {
            $myFile = fopen($newFileName, "w");
            while (($line = fgets($handle)) !== false) {
                $changedStr = "defaultPhpNamingMethod=\"underscore\" namespace=\"PhpFramework\Model\\$dbPascalName\"";
                $newLine = str_replace("defaultPhpNamingMethod=\"underscore\"", $changedStr, $line);
                fwrite($myFile, $newLine);
            }

            fclose($myFile);
            fclose($handle);
            unlink($oriFileName);
            rename($newFileName, $oriFileName);
        }
        sleep(1);

        ConsoleLog::doPrintMessage("black", "white", "step (4/6) \t: model build", 1);
        Console::doCommandExec("./vendor/propel/propel/bin/propel model:build --schema-dir ./generated-reversed-database --output-dir ./src/");
        sleep(1);

        ConsoleLog::doPrintMessage("black", "white", "step (5/6) \t: create config.php.", 1);
        Console::doCommandExec("./vendor/propel/propel/bin/propel config:convert --output-dir ./src/Resources/Database");
        sleep(1);

        ConsoleLog::doPrintMessage("black", "white", "step (6/6) \t: delete reversed database", 2);
        Console::doCommandExec("rm -rf ./generated-reversed-database");
    }

    /**
     * @param $dbHost
     * @param $dbName
     * @param $dbUser
     * @param $dbPass
     */
    public function doValidationConnect($dbHost, $dbName, $dbUser, $dbPass)
    {
        $connection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
        if (!$connection) {
            ConsoleLog::errorMessage("The `database` connection failed");
            exit;
        }
    }

    /**
     *
     */
    public function doValidationPropelInstall()
    {
        if (!file_exists('./vendor/propel/propel/bin/propel')) {
            ConsoleLog::errorMessage("The `database` command needs a few 'propel' requirements. Please install `propel` library first. More information, Please visit here(`http://www.propelorm.org/`)");
            exit;
        }

    }
}