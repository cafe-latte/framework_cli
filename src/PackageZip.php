<?php

namespace Cafelatte\PackageManager;



class PackageZip
{

    PRIVATE $uploadPath;
    PRIVATE $inputFile;
    PRIVATE $outputFile;
    PRIVATE $prefixFolder;

    CONST ZIP_BIN = "/usr/bin/zip";
    CONST UNZIP_BIN = "/usr/bin/unzip";


    public function __construct()
    {
        $this->prefixFolder = null;
    }

    /**
     *
     * @param string $inputFile
     */
    public function setInputFile(string $inputFile)
    {

        $this->inputFile = $inputFile;
    }

    /**
     *
     * @param string $outputFile
     */
    public function setOutputFile(string $outputFile)
    {
        $this->outputFile = $outputFile;
    }

    /**
     *
     * @param string $uploadPath
     */
    public function setUploadPath(string $uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }


    /**
     * 압축 실행
     * 
     * @return string
     */
    public function doZipExe()
    {
        $command = self::ZIP_BIN . " -r " . $this->uploadPath . $this->outputFile . " " . $this->inputFile;
        return Console::doCommandExec($command);
    }

    /**
     * 압축 풀기
     *
     * @return string
     */
    public function doUnZipExe()
    {
        $command = self::UNZIP_BIN . " " . $this->inputFile . " -d " . $this->outputFile;
        return Console::doCommandExec($command);
    }


}
