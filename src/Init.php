<?php

namespace Cafelatte\PackageManager;

include "./ConsoleLog.php";
include "./Console.php";
include "./CafeLatte.php";
include "./PackageSetup.php";
include "./PackageArgvParser.php";
include "./PackageZip.php";
include "./PackageBuilder.php";
include "./PackageDatabase.php";
include "./PackageInit.php";
include "./Repository.php";
include "./TemplateGenerate.php";


class Init extends PackageArgvParser
{

    public $params;

    /**
     * Init constructor.
     * @param $argv
     */
    public function __construct($argv)
    {
        error_reporting(0);
        $this->params = $this->doParser($argv);
        $this->doCommand();
    }

    /**
     * @return void
     */
    public function doCommand()
    {
        if ($this->params[0]) {
            switch ($this->params[0]) {
                case "version":
                    $this->getVersion();
                    break;
                case "check":
                    $this->getLogo();
                    $inst = new CafeLatte();
                    $inst->doPreCheckComposer();
                    $inst->doPreCheckCafeLatteJson($this->params['config']);
                    $inst->doPreCheckFolder($this->params[2]);
                    $inst->doPreCheckDatabase($this->params[2]);
                    $result = $inst->doPreCheckResult();
                    ConsoleLog::noticeMessage("Checking result........................");
                    if ($result == true) {
                        ConsoleLog::doPrintFile("Final result ", "OK", "green", "Result...");
                    } else {
                        ConsoleLog::doPrintFile("Final result ", "ERROR", "red", "Result...");
                    }
                    echo PHP_EOL.PHP_EOL;
                    break;
                case "setup":
                    $inst = new CafeLatte();
                    $inst->doPreCheckComposer();
                    $inst->doPreCheckCafeLatteJson($this->params['config']);
                    $inst->doPreCheckFolder($this->params[2]);
                    $inst->doPreCheckDatabase($this->params[2]);

                    $result = $inst->doPreCheckResult();
                    ConsoleLog::noticeMessage("Checking result........................");
                    echo PHP_EOL.PHP_EOL;
                    if ($result == true) {
                        ConsoleLog::doPrintFile("Final result ", "OK", "green", "Result...", 3);

                        $inst->doSetup($this->params[2]);
                        $inst = new PackageInit();
                        $inst->doInit($this->params[2]);
                    } else {
                        ConsoleLog::doPrintFile("Final result ", "ERROR", "red", "Result...");
                        ConsoleLog::errorMessage("---CAN NOT MOVE A NEXT STEP MORE, PLEASE SOLVE SOME ERRORS---");
                    }

                    echo PHP_EOL.PHP_EOL;
                    break;
                case "remove":
                    $inst = new CafeLatte();
                    $inst->doRemove($this->params[2]);
                    echo PHP_EOL.PHP_EOL;
                    break;


                case "template":
                    $template = new TemplateGenerate();
                    switch ($this->params[1]) {
                        case "clean":
                            $template->doClean($this->params[2]);
                            echo PHP_EOL;
                            break;
                        case "create":
                            $template->doCompile($this->params[2]);
                            echo PHP_EOL;
                            break;
                        case "update":
                            $template->doClean($this->params[2]);
                            $template->doCompile($this->params[2]);
                            echo PHP_EOL;
                            break;
                        case "delete":
                            $template->doDelete($this->params[2]['template']['output']);
                            echo PHP_EOL;
                            break;
                        default:
                            ConsoleLog::errorMessage("Do not support this command(`" . $this->params[1] . "`)");
                            break;
                    }
                    break;

                case "database":
                    switch ($this->params[1]) {
                        case "update":
                            $inst = new PackageDatabase();
                            $inst->doUpdate($this->params[2]);
                            echo PHP_EOL;
                            break;
                        default:
                            ConsoleLog::errorMessage("Do not support this command(`" . $this->params[1] . "`)");
                            break;
                    }
                    break;

                default:
                    ConsoleLog::errorMessage("Do not support this command(`" . $this->params[0] . "`)");
                    break;
            }
        } else {
            $this->getVersion();
            $this->getUsage();
            $this->getAvailableCommand();
            $this->getOption();
        }
    }

    /**
     *
     */
    public function getLogo()
    {
        ConsoleLog::doPrintMessage("", "cyan", "@@@@@   @   @@@@@ @@@@@ @       @   @@@@@ @@@@@ @@@@@", 1);
        ConsoleLog::doPrintMessage("", "cyan", "@      @ @  @     @     @      @ @    @     @   @    ", 1);
        ConsoleLog::doPrintMessage("", "cyan", "@     @@@@@ @@@@  @@@@@ @     @@@@@   @     @   @@@@@", 1);
        ConsoleLog::doPrintMessage("", "cyan", "@     @   @ @     @     @     @   @   @     @   @    ", 1);
        ConsoleLog::doPrintMessage("", "cyan", "@@@@@ @   @ @     @@@@@ @@@@@ @   @   @     @   @@@@@", 2);
    }


    /**
     *
     */
    public function getVersion()
    {
        ConsoleLog::doPrintMessage("yellow", "black", "Do not run `cafelatte` as root/super user", 2);

        ConsoleLog::doPrintMessage("black", "white", "This file is part of CafeLatte framework and including a package manager functions", 1);
        ConsoleLog::doPrintMessage("black", "white", "This binary helps you to convert your HTML code into PHP output statements", 2);

        $this->getLogo();

        ConsoleLog::doPrintMessage("", "cyan", "Name    : CafeLatte Binary", 1);
        ConsoleLog::doPrintMessage("", "cyan", "Version", 0);
        ConsoleLog::doPrintMessage("", "cyan", " : v1.1.3", 1);
        ConsoleLog::doPrintMessage("", "cyan", "Url    ", 0);
        ConsoleLog::doPrintMessage("", "cyan", " : https://www.cafe-latte.co.kr", 1);
        ConsoleLog::doPrintMessage("", "cyan", "Author ", 0);
        ConsoleLog::doPrintMessage("", "cyan", " : Thorpe Lee", 2);
    }

    /**
     * @return void
     */
    public function getUsage()
    {
        ConsoleLog::doPrintMessage("black", "yellow", "Usage:", 1);
        echo "\e[0m  cafelatte check [options=arguments]" . PHP_EOL;
        echo "\e[0m  cafelatte setup [options=arguments]" . PHP_EOL;
        echo "\e[0m  cafelatte remove [options=arguments]" . PHP_EOL;
        echo "\e[0m  cafelatte template [init/update/delete] [options=arguments]" . PHP_EOL;
        echo "\e[0m  cafelatte package [clean/create/install/delete/build] [options=arguments]" . PHP_EOL;
        echo "\e[0m  cafelatte database [update] [options=arguments]" . PHP_EOL;
        echo "\e[0m  cafelatte composer [update] [options=arguments]" . PHP_EOL;
        echo "\e[0m  cafelatte version" . PHP_EOL;
        echo "\e[0m  ex) \e[33mcafelatte template update --config=cafelatte.json \e[0m" . PHP_EOL . PHP_EOL;
    }

    /**
     *
     */
    public function getOption()
    {
        ConsoleLog::doPrintMessage("black", "yellow", "Option:", true);
        echo "\e[32m  --config\e[0m\t\t the default value is cafelatte.json" . PHP_EOL . PHP_EOL;
    }

    /**
     *
     */
    public function getAvailableCommand()
    {
        ConsoleLog::doPrintMessage("black", "yellow", "First commands:", true);
        echo "\e[32m  template\e[0m\t\t Convert html to php" . PHP_EOL;
        echo "\e[32m    ├─ init\e[0m\t\t Use template init command" . PHP_EOL;
        echo "\e[32m    ├─ update\e[0m\t\t Use template update command " . PHP_EOL;
        echo "\e[32m    └─ build\e[0m\t\t Use template build command " . PHP_EOL . PHP_EOL;
        echo "\e[32m  database\e[0m\t\t Use database command" . PHP_EOL;
        echo "\e[32m    └─update\e[0m\t\t Use database update command " . PHP_EOL . PHP_EOL;
        echo "\e[32m  version\e[0m\t\t Show version info " . PHP_EOL . PHP_EOL;
    }


}


new Init($argv);
