<?php

namespace Cafelatte\PackageManager;


class PackageDatabase
{

    public function __construct()
    {

    }


    /**
     * @param $jsonData
     */
    public function doUpdate($jsonData)
    {
        $dbHost = $jsonData['database']['db_host'];
        $dbName = $jsonData['database']['db_name'];
        $dbUser = $jsonData['database']['db_user'];
        $dbPass = $jsonData['database']['db_pass'];

        $this->doValidationConnect($dbHost, $dbName, $dbUser, $dbPass);
        $this->doValidationPropelInstall();

        Console::doCommandExec("chmod 777 ./vendor/propel/propel/bin/propel");
        Console::doCommandExec("./vendor/propel/propel/bin/propel reverse --database-name {$dbName} \"mysql:host={$dbHost};dbname={$dbName};user={$dbUser};password={$dbPass}\"");
        Console::doCommandExec("find ./generated-reversed-database/schema.xml -name \"*.*\" -exec sed -i \"s/defaultPhpNamingMethod=\"underscore\"/defaultPhpNamingMethod=\"underscore\" namespace=\"PhpFramework\\\\\Model\"/g\" {} \;");
        Console::doCommandExec("./vendor/propel/propel/bin/propel model:build --schema-dir ./generated-reversed-database --output-dir ./src/");
        Console::doCommandExec("rm -rf ./generated-reversed-database");
        Console::doCommandExec("./vendor/propel/propel/bin/propel config:convert --output-dir ./src/Resources/Database/");
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