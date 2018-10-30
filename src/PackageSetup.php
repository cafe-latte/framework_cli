<?php

namespace Cafelatte\PackageManager;


class PackageSetup
{

    private $folders;
    private $checkResult = true;


    public function __construct()
    {
        $this->doPackageDefaultFolder();
    }

    /**
     *
     */
    public function doPackageDefaultFolder()
    {
        $this->folders = array(
            "./src",
            "./src/PhpFramework",
            "./src/PhpFramework/Config",
            "./src/PhpFramework/Controllers",
            "./src/PhpFramework/Controllers/Admin",
            "./src/PhpFramework/Controllers/Www",
            "./src/PhpFramework/Exceptions",
            "./src/PhpFramework/Helpers",
            "./src/PhpFramework/Layout",
            "./src/PhpFramework/Libraries",
            "./src/PhpFramework/Model",
            "./src/PhpFramework/Routes",
            "./src/PhpFramework/Service",
            "./src/Resources",
            "./src/Resources/Database",
            "./web",
            "./web/Admin",
            "./web/Www",
        );
    }

    /**
     *
     */
    public function doPreCheckComposer()
    {
        ConsoleLog::noticeMessage("Checking Propel........................");
        $isInstallComposer = Console::doCommandExec("which composer");
        if ($isInstallComposer) {
            ConsoleLog::doPrintFile("the Composer Installed", "OK", "green", "Checking...");
        } else {
            $this->checkResult = false;
            ConsoleLog::doPrintFile("the Composer Installed", "ERROR(NOT INSTALLED)", "red", "Checking...");
        }

        echo PHP_EOL . PHP_EOL;
        sleep(1);
    }

    /**
     * @param $configFileName
     */
    public function doPreCheckCafeLatteJson($configFileName)
    {
        ConsoleLog::noticeMessage("Checking cafelatte.json file...........");

        if ($configFileName) {
            if (is_file($configFileName)) {
                ConsoleLog::doPrintFile(" `{$configFileName}` Existed", "OK", "green", "Checking...");
            } else {
                $this->checkResult = false;
                ConsoleLog::doPrintFile("`{$configFileName}` Existed", "ERROR(NOT EXISTED)", "red", "Checking...");
            }
        } else {
            if (is_file("cafelatte.json")) {
                ConsoleLog::doPrintFile("`cafelatte.json` Existed", "OK", "green", "Checking...");
            } else {
                $this->checkResult = false;
                ConsoleLog::doPrintFile("`cafelatte.json` Existed", "ERROR(NOT EXISTED)", "red", "Checking...");
            }
        }

        echo PHP_EOL . PHP_EOL;
        sleep(1);
    }

    /**
     * @param null $jsonData
     */
    public function doPreCheckFolder($jsonData = null)
    {
        ConsoleLog::noticeMessage("Checking Folder And File In Default....");

        foreach ($this->folders as $folder) {
            if (file_exists($folder) != true) {
                ConsoleLog::doPrintFile($folder, "OK", "green", "Checking...");
            } else {
                $this->checkResult = false;
                ConsoleLog::doPrintFile("`$folder` Folder(or File) Existed", "ERROR(EXISTED)", "red", "Checking...");
            }
        }

        if ($jsonData != null) {
            if ($jsonData['log']['path']) {
                if (file_exists($jsonData['log']['path']) != true) {
                    ConsoleLog::doPrintFile($jsonData['log']['path'], "OK", "green", "Checking...");
                }
            }

            if ($jsonData['template']['on_off'] == 'on') {
                if ($jsonData['upload']['path']) {
                    if (file_exists($jsonData['upload']['path']) != true) {
                        ConsoleLog::doPrintFile($jsonData['upload']['path'], "OK", "green", "Checking...");
                    }
                } else {
                    ConsoleLog::doPrintFile($jsonData['upload']['path'], "OK", "green", "Checking...");
                }
            }

            if ($jsonData['template']['on_off'] == 'on') {
                if ($jsonData['template']['input']) {
                    if (file_exists($jsonData['template']['input']) != true) {
                        ConsoleLog::doPrintFile($jsonData['template']['input'], "OK", "green", "Checking...");
                    }
                } else {
                    ConsoleLog::doPrintFile($jsonData['upload']['path'], "OK", "green", "Checking...");
                }

                if ($jsonData['template']['output']) {
                    if (file_exists($jsonData['template']['output']) != true) {
                        ConsoleLog::doPrintFile($jsonData['template']['output'], "OK", "green", "Checking...");
                    }
                } else {
                    ConsoleLog::doPrintFile($jsonData['upload']['path'], "OK", "green", "Checking...");
                }
            }


            if ($jsonData['project']['public_html']) {
                if (file_exists($jsonData['project']['public_html']) != true) {
                    ConsoleLog::doPrintFile($jsonData['project']['public_html'], "OK", "green", "Checking...");
                }
            }

            if ($jsonData['tmp']['path']) {
                if (file_exists($jsonData['tmp']['path']) != true) {
                    ConsoleLog::doPrintFile($jsonData['tmp']['path'], "OK", "green", "Checking...");
                }
            }
        }

        echo PHP_EOL . PHP_EOL;
        sleep(1);

    }

    /**
     * @param null $jsonData
     */
    public function doPreCheckDatabase($jsonData = null)
    {
        ConsoleLog::noticeMessage("Checking Database Connection...........");

        $dbHost = $jsonData['database']['db_host'];
        $dbName = $jsonData['database']['db_name'];
        $dbUser = $jsonData['database']['db_user'];
        $dbPass = $jsonData['database']['db_pass'];

        $connection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
        if (!$connection) {
            ConsoleLog::doPrintFile("`$dbName` Database Connection", "OK", "green", "Checking...");
        } else {
            $this->checkResult = false;
            ConsoleLog::doPrintFile("`$dbName` Database Connection", "ERROR(FAILED)", "red", "Checking...");
        }

        echo PHP_EOL . PHP_EOL;
        sleep(1);
    }


    /**
     * @return bool
     */
    public function doPreCheckResult()
    {
        return $this->checkResult;
    }


    /**
     * @param null $jsonData
     */
    public function doSetup($jsonData = null)
    {
        $answer = Console::doCommand("Do you create all folders/files? (yes/no) yes : ");
        if (!$answer) {
            $answer = "yes";
        }

        if ($answer == "yes") {
            foreach ($this->folders as $folder) {
                if (file_exists($folder) != true) {
                    $command = "mkdir " . $folder;
                    Console::doCommandExec($command);
                }
            }
        }

        if ($jsonData != null) {
            if ($jsonData['log']['path']) {
                if (file_exists($jsonData['log']['path']) != true) {
                    $command = "mkdir " . $jsonData['log']['path'];
                    Console::doCommandExec($command);
                }
                $command = "chmod 777 " . $jsonData['log']['path'];
                Console::doCommandExec($command);
            }

            if ($jsonData['upload']['path']) {
                if (file_exists($jsonData['upload']['path']) != true) {
                    $command = "mkdir " . $jsonData['upload']['path'];
                    Console::doCommandExec($command);
                }
                $command = "chmod 777 " . $jsonData['upload']['path'];
                Console::doCommandExec($command);
            }

            if ($jsonData['template']['input']) {
                if (file_exists($jsonData['template']['input']) != true) {
                    $command = "mkdir " . $jsonData['template']['input'];
                    Console::doCommandExec($command);
                }
            }

            if ($jsonData['template']['output']) {
                if (file_exists($jsonData['template']['output']) != true) {
                    $command = "mkdir " . $jsonData['template']['output'];
                    Console::doCommandExec($command);
                }
                $command = "chmod 777 " . $jsonData['template']['output'];
                Console::doCommandExec($command);

            }

            if ($jsonData['project']['public_html']) {
                if (file_exists($jsonData['project']['public_html']) != true) {
                    $command = "mkdir " . $jsonData['project']['public_html'];
                    Console::doCommandExec($command);
                }
            }

            if ($jsonData['project']['public_html']) {
                if (file_exists($jsonData['project']['public_html']) != true) {
                    $command = "mkdir " . $jsonData['project']['public_html'];
                    Console::doCommandExec($command);
                }
            }
        }


    }

    /**
     * @param $jsonData
     */
    public function doRemove($jsonData)
    {
        $answer = Console::doCommand("Do you delete all folders/files? (yes/no) yes : ");
        if (!$answer) {
            $answer = "yes";
        }

        if ($answer == "yes") {
            foreach ($this->folders as $folder) {
                if (file_exists($folder) == true) {
                    Console::doCommandExec("rm -rf " . $folder);
                }
            }

            if ($jsonData != null) {
                if ($jsonData['log']['path']) {
                    if (file_exists($jsonData['log']['path']) == true) {
                        $command = "rm -rf " . $jsonData['log']['path'];
                        Console::doCommandExec($command);
                    }
                }

                if ($jsonData['upload']['path']) {
                    if (file_exists($jsonData['upload']['path']) == true) {
                        $command = "rm -rf " . $jsonData['upload']['path'];
                        Console::doCommandExec($command);
                    }
                }

                if ($jsonData['template']['input']) {
                    if (file_exists($jsonData['template']['input']) == true) {
                        $command = "rm -rf " . $jsonData['template']['input'];
                        Console::doCommandExec($command);
                    }
                }

                if ($jsonData['template']['output']) {
                    if (file_exists($jsonData['template']['output']) == true) {
                        $command = "rm -rf " . $jsonData['template']['output'];
                        Console::doCommandExec($command);
                    }
                }

                if ($jsonData['project']['public_html']) {
                    if (file_exists($jsonData['project']['public_html']) == true) {
                        $command = "rm -rf " . $jsonData['project']['public_html'];
                        Console::doCommandExec($command);
                    }
                }

                if ($jsonData['temp']['path']) {
                    if (file_exists($jsonData['temp']['temp']) == true) {
                        $command = "rm -rf " . $jsonData['temp']['temp'];
                        Console::doCommandExec($command);
                    }
                }
            }


            if (file_exists("composer.json") == true) {
                $command = "rm -rf composer.json";
                Console::doCommandExec($command);
            }

            if (file_exists("composer.lock") == true) {
                $command = "rm -rf composer.lock";
                Console::doCommandExec($command);
            }

            if ($jsonData['database']['on_off'] == 'on') {
                if (file_exists('./propel.php') == true) {
                    $command = "rm -rf propel.php";
                    Console::doCommandExec($command);
                }
            }

        }
    }
}