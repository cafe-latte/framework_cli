<?php

namespace Cafelatte\PackageManager;


class PackageInit
{

    public function __construct()
    {
    }

    public function getComposerFile($jsonData)
    {
        $body = <<<EOF
{
  "name": "{$jsonData['project']['name']}",
  "description": "{$jsonData['project']['name']}",
  "require": {
    "php": ">=7.0",
    "cafelatte/framework": "v1.0.*",
    "cafelatte/library": "v1.0.*",
    "propel/propel": "~2.0@dev"    
  },
  "autoload": {
    "psr-4": {
      "PhpFramework\\\\": "src/PhpFramework"
    }
  }
}


EOF;
        return $body;
    }

    public function getHtaccessFile()
    {
        $body = <<<EOF
Options +FollowSymLinks
IndexIgnore */*
# Turn on the RewriteEngine
RewriteEngine On
#  Rules
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/(favicon\.ico)$ [NC]
RewriteRule . index.php

EOF;
        return $body;
    }


    public function getIndexFile($routeName, $jsonData)
    {
        $routeName = $routeName. "Route";
        $body = <<<EOF
<?php

date_default_timezone_set('Asia/Seoul');
ini_set("display_errors", 1);
error_reporting('E_ALL');

require_once("{$jsonData['project']['path']}vendor/autoload.php");
require_once("{$jsonData['project']['path']}src/Resources/Database/config.php");

\$framework = new \PhpFramework\Routes\\$routeName("{$jsonData['project']['path']}cafelatte.json");
\$framework->execute();

EOF;
        return $body;
    }

    public function getRouteSampleCode($controllerName, $routeName)
    {
        $routeName = $routeName. "Route";
        $controllerName = $controllerName. "Controller";
        $body = <<<EOF
<?php

namespace PhpFramework\Routes;


use CafeLatte\Core\BaseRoute;
use CafeLatte\Interfaces\RouterInterface;
use PhpFramework\Controllers\Admin\\$controllerName;

class $routeName extends BaseRoute implements RouterInterface
{
    public function routing()
    {
        /**
         * Sample Code
         * ---------------------------------------------------------------------
         */
        \$this->router->get("", function () {
             \$this->result = (new $controllerName(\$this->request, \$this->log))->sample();
        });
     

        /**
         * 실행
         * ---------------------------------------------------------------------
         */
       \$this->router->run();
    }
}

EOF;
        return $body;
    }


    public function getControllerSampleCode($controllerName)
    {
        $controllerName = $controllerName. "Controller";
        $body = <<<EOF
<?php

namespace PhpFramework\Controllers\Admin;


use CafeLatte\Core\Controller;
use CafeLatte\Core\Response;
use CafeLatte\Interfaces\ControllerInterface;
use CafeLatte\Interfaces\HttpRequestInterface;
use CafeLatte\Interfaces\LoggerInterface;


class $controllerName extends Controller implements ControllerInterface
{
    /**
     * __construct
     *
     * LoginController constructor.
     * @param HttpRequestInterface \$request
     * @param LoggerInterface \$log
     */
    public function __construct(HttpRequestInterface \$request, LoggerInterface \$log)
    {
        parent::__construct(\$request, \$log);
    }

    /**
     * sample method code
     *
     */
    public function sample()
    {
        return Response::create()->setResponseType("json")->run();
    }
  
}

EOF;
        return $body;
    }


    public function getPropelFile($jsonData)
    {
        $dbHost = $jsonData['database']['db_host']['0'];
        $dbName = $jsonData['database']['db_name'];
        $dbUser = $jsonData['database']['db_user'];
        $dbPass = $jsonData['database']['db_pass'];

        $body = <<<EOF
<?php

return [
    'propel' => [
        'database' => [
            'connections' => [
                '$dbName' => [
                    'adapter' => 'mysql',
                    'classname' => 'Propel\Runtime\Connection\ConnectionWrapper',
                    'dsn' => 'mysql:host=$dbHost;dbname=$dbName;charset=utf8',
                    'user' => '$dbUser',
                    'password' => '$dbPass'
                ]
            ]
        ],
        'runtime' => [
            'defaultConnection' => '$dbName',
            'connections' => ['$dbName']
        ],
        'generator' => [
            'defaultConnection' => '$dbName',
            'connections' => ['$dbName'],
            'namespaceAutoPackage' => true,
            'schema' => [
                'autoPackage' => true
            ]
        ]
    ]
];

EOF;
        return $body;
    }

    /**
     * @param null $jsonData
     */
    public function doInit($jsonData = null)
    {
        $composerFileName = $jsonData['project']['path'] . "composer.json";
        $indexFileName = $jsonData['project']['public_html'] . "index.php";
        $htaccessFileName = $jsonData['project']['public_html'] . ".htaccess";

        $controllerName = ucfirst(strtolower(Console::doCommand("Please enter a new controller class name : ")));
        $controllerFileName = "./src/PhpFramework/Controllers/Admin/" . $controllerName . "Controller.php";

        $routeName = ucfirst(strtolower(Console::doCommand("Please enter a new route class name : ")));
        $routeFileName = "./src/PhpFramework/Routes/" . $routeName . "Route.php";


        $this->doCreateFile($composerFileName, $this->getComposerFile($jsonData));
        $this->doCreateFile($htaccessFileName, $this->getHtaccessFile());
        $this->doCreateFile($indexFileName, $this->getIndexFile($routeName, $jsonData));
        $this->doCreateFile($controllerFileName, $this->getControllerSampleCode($controllerName));
        $this->doCreateFile($routeFileName, $this->getRouteSampleCode($controllerName, $routeName));
    }




    /**
     * Create File
     *
     * @param $filePathName
     * @param $fileCode
     */
    public function doCreateFile($filePathName, $fileCode)
    {
        if (is_file($filePathName)) {
            $answer = Console::doCommand("`{$filePathName}` existed. Do you want to overwrite it? 'yes | no': ");
            if (\strtoupper($answer) == "YES" || \strtoupper($answer) == "Y") {
                touch($filePathName);
                $handle = fopen($filePathName, 'wb');
                fwrite($handle, $fileCode);
                fclose($handle);

                Console::doCommandExec("chmod 644 {$filePathName}");
                ConsoleLog::doPrintFile($filePathName, "CHANGE", "green", "UPDATE");
            } else {
                ConsoleLog::doPrintFile($filePathName, "NO CHANGE", "black", "UPDATE");
            }
        } else {
            touch($filePathName);
            $handle = fopen($filePathName, 'wb');
            fwrite($handle, $fileCode);
            fclose($handle);

            Console::doCommandExec("chmod 644 {$filePathName}");
            ConsoleLog::doPrintFile($filePathName, "CREATE", "green", "CREATE");
        }
        ConsoleLog::doPrintMessage("reset", "black", "", 0);
    }
}