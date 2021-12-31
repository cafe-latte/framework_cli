<?php
$srcRoot = "./src";
$buildRoot = "./build";

$extension = ['php'];
$dir = __DIR__;



$file = 'cafelatte.phar';

$phar = new Phar(__DIR__ . '/' . $file, 0, $file);
$phar->startBuffering();

foreach ($extension as $ext) {
    $phar->buildFromDirectory($dir, '/\.' . $ext . '$/');
}

$phar->setStub("#!/usr/bin/env php" . PHP_EOL . "
<?php
    /*
     * This file is part of CafeLatte Package Manager.
     *
     * (c) Thorpe Lee <koangbok@gmail.com>, <https://www.cafe-latte.co.kr>
     * CafeLatte(CL) Package Manager is released under the `MIT` license.
     * License : MIT
     */
     
    if (version_compare('7.0.0', PHP_VERSION, '>')) {
        fwrite(
            STDERR,
            sprintf(
                'This version of PHPCPD is supported on PHP 7.0, and PHP 7.1.' . PHP_EOL .
                'You are using PHP %s%s.' . PHP_EOL,
                PHP_VERSION,
                defined('PHP_BINARY') ? ' (' . PHP_BINARY . ')' : ''
            )
        );
        die(1);
    }    

    
    Phar::mapPhar('{$file}');
    require 'phar://{$file}/src/Init.php';
    __HALT_COMPILER();" . PHP_EOL . "
?>");


$phar->stopBuffering();


exec("chmod 777 " . $file);
exec("mv " . $file  . " ". $buildRoot . "/cafelatte.phar" );
exec("cp " . $buildRoot . "/cafelatte.phar"  . " /usr/local/bin/cafelatte");
exec("chmod 777 /usr/local/bin/cafelatte");
echo "Binary Complete...\n";