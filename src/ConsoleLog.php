<?php

namespace Cafelatte\PackageManager;


class ConsoleLog
{

    /**
     * @param string $message
     * @param string $result
     * @param string $type
     * @param string $action
     * @param int $lineCount
     */
    public static function doPrintFile(string $message, string $result = 'OK', string $type = "green", string $action = "CREATE", int $lineCount = 1)
    {
        $mask = "%-15.15s => %-80.80s -------------- %-20.20s";
        switch ($type) {
            case "green":
                printf($mask, "\e[0m {$action}", $message, "\e[32m{$result}");
                break;
            case "red":
                printf($mask, "\e[0m {$action}", $message, "\e[31m{$result}");
                break;
            default:
                printf($mask, "\e[0m {$action}", $message, "\e[0m{$result}");
                break;
        }

        if ($lineCount > 0) {
            for ($i = 0; $i < $lineCount; $i++) {
                echo PHP_EOL;
            }
        }

        echo "\e[0m";
    }


    /**
     * @param string $bgColor
     * @param string $fontColor
     * @param string $message
     * @param int $lineCount
     */
    public static function doPrintMessage(string $bgColor, string $fontColor, string $message, int $lineCount = 0)
    {
        switch ($bgColor) {
            case "reset":
                echo "\e[0m";
                break;
            case "black":
                echo "\e[40m";
                break;
            case "red":
                echo "\e[41m";
                break;
            case "green":
                echo "\e[42m";
                break;
            case "yellow":
                echo "\e[43m";
                break;
            case "blue":
                echo "\e[44m";
                break;
            case "purple":
                echo "\e[45m";
                break;
            case "cyan":
                echo "\e[46m";
                break;
            case "white":
                echo "\e[47m";
                break;
        }

        switch ($fontColor) {
            case "reset":
                echo "\e[0m{$message}\e[0m";
                break;
            case "black":
                echo "\e[30m{$message}\e[0m";
                break;
            case "red":
                echo "\e[31m{$message}\e[0m";
                break;
            case "green":
                echo "\e[32m{$message}\e[0m";
                break;
            case "yellow":
                echo "\e[33m{$message}\e[0m";
                break;
            case "blue":
                echo "\e[34m{$message}\e[0m";
                break;
            case "purple":
                echo "\e[35m{$message}\e[0m";
                break;
            case "cyan":
                echo "\e[36m{$message}\e[0m";
                break;
            case "white":
                echo "\e[37m{$message}\e[0m";
                break;
        }

        if ($lineCount > 0) {
            for ($i = 0; $i < $lineCount; $i++) {
                echo PHP_EOL;
            }
        }

    }

    public static function errorMessage($message) {
        ConsoleLog::doPrintMessage("red", "white", "ERROR : " . $message, 2);
    }

    public static function noticeMessage($message) {
        ConsoleLog::doPrintMessage("green", "white", $message, 2);
    }

}