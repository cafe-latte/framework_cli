<?php

namespace Cafelatte\PackageManager;

class PackageArgvParser
{

    /**
     * ArgvParser constructor.
     */
    public function __construct()
    {
    }



    /**
     * @param $argv
     * @return array
     */
    public function doParser($argv): array
    {
        array_shift($argv);
        $out = array();
        foreach ($argv as $arg) {
            if (substr($arg, 0, 2) == '--') {
                $eqPos = strpos($arg, '=');
                if ($eqPos === false) {
                    $key = substr($arg, 2);
                    $value = isset($out[$key]) ? $out[$key] : true;
                    $out[$key] = $value;
                } else {
                    $key = substr($arg, 2, $eqPos - 2);
                    $value = substr($arg, $eqPos + 1);
                    $out[$key] = $value;
                }
            } else if (substr($arg, 0, 1) == '-') {

                if (substr($arg, 2, 1) == '=') {
                    $key = substr($arg, 1, 1);
                    $value = substr($arg, 3);
                    $out[$key] = $value;
                } else {
                    $chars = str_split(substr($arg, 1));
                    foreach ($chars as $char) {
                        $key = $char;
                        $value = isset($out[$key]) ? $out[$key] : true;
                        $out[$key] = $value;
                    }
                }
            } else {
                $value = $arg;
                $out[] = $value;
            }
        }


        if (isset($out['config'])) {
            $jsonInfo = json_decode(file_get_contents($out['config']), true);
            if ($jsonInfo) {
                return array($argv[0], $argv[1], $jsonInfo);
            }
        } else {
            if (is_file("cafelatte.json")) {
                $jsonInfo = json_decode(file_get_contents("cafelatte.json"), true);
                if ($jsonInfo) {
                    return array($argv[0], $argv[1], $jsonInfo);
                }
            }
        }

        return $out;
    }
}