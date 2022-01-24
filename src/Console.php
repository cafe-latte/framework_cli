<?php
/**
 * Created by PhpStorm.
 * User: sejoong
 * Date: 2017-12-18
 * Time: 오전 10:24
 */

namespace Cafelatte\PackageManager;


class Console
{
    /**
     * @param $command
     * @return string
     */
    public static function doCommand($command): string
    {
        echo $command;
        return rtrim(fgets(STDIN));
    }

    /**
     * @param $command
     * @return false|string|null
     */
    public static function doCommandExec($command)
    {
        return shell_exec($command);
    }

    /**
     * @param int $sec
     * @return void
     */
    public static function doWait(int $sec)
    {
        sleep($sec);
    }
}