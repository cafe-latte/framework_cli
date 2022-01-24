<?php

namespace Cafelatte\PackageManager;


class PackageBuilder
{
    CONST TEMP_FOLDER = "./tmp/";
    CONST TEMP_FILENAME = "cafelatte.zip";


    public function __construct()
    {

    }

    /**
     * @param $jsonData
     */
    public function doBuild($jsonData)
    {
        ConsoleLog::doPrintMessage("green", "white", "STEP 01. 현재 작업중인 파일을 임시 폴더에 복사합니다. (SRC->TEMP)", 2);

        for ($z = 0; $z < count($jsonData['package']); $z++) {
            ConsoleLog::doPrintFile($jsonData['package'][$z]['name'], "OK", $type = "green", $action = "PACKAGE");

            foreach ($jsonData['package'][$z]['builder']['src'] as $key => $value) {
                foreach ($value as $value2) {
                    $file = "./src/PhpFramework/" . $key . "/" . $value2;

                    if (is_file($file)) {
                        $delimiterFolders = explode("/", $file);

                        $folderFile = "";
                        for ($i = 0; $i < count($delimiterFolders); $i++) {
                            $folderFile .= str_replace("src", "tmp", $delimiterFolders[$i]);
                            if ($i != count($delimiterFolders) - 1) {

                                if (!file_exists($folderFile)) {
                                    Console::doCommandExec("mkdir " . $folderFile);
                                }
                                $folderFile .= "/";
                            } else {
                                if (!file_exists($folderFile)) {
                                    Console::doCommandExec("cp  " . $file . " " . $folderFile);
                                    ConsoleLog::doPrintFile($folderFile, "OK", $type = "green", $action = "");
                                }
                            }
                        }
                        unset($i);
                        unset($folderFile);

                    } else {
                        ConsoleLog::doPrintFile($file, "FAIL(NO EXIST FILE)", $type = "red", $action = "");
                    }
                }
            }

            $this->doZip(self::TEMP_FOLDER . self::TEMP_FILENAME);
            $this->doSendZipFileToRepo(self::TEMP_FOLDER . self::TEMP_FILENAME, $jsonData['package'][$z]);
            $this->doDeleteTempFile();

            ConsoleLog::doPrintMessage("green", "white", "", 1);
        }

        ConsoleLog::doPrintMessage("green", "white", "", 2);
    }

    /**
     * @param $fileName
     */
    public function doZip($fileName)
    {
        $zip = new PackageZip();
        $zip->setInputFile(self::TEMP_FOLDER . "*");
        $zip->setOutputFile($fileName);
        $zip->doZipExe();
    }

    /**
     * @param string $fileName
     * @param array $jsonData
     * @return mixed
     */
    public function doSendZipFileToRepo(string $fileName, array $jsonData = [])
    {
        $params = array('packageName' => $jsonData['name'], 'jsonData' => json_encode($jsonData['builder']), 'file' => new \CURLFile($fileName));
        if ($jsonData['server'] == 'develop') {
            $url = Repository::URL_DEVELOP . "/Repo/Upload";
        } else {
            $url = Repository::URL_PRODUCT . "/Repo/Upload";
        }

        return Repository::doCurl($url, "POST", $params);
    }

    /**
     *
     */
    public function doDeleteTempFile()
    {
        sleep(2);
        Console::doCommandExec("rm -rf ./tmp/*");

    }
}