<?php

namespace SITE\Helpers;


use A7\A7;
use A7\ReflectionUtils;
use SITE\Exceptions\UndefinedMethodException;
use SITE\Helpers\Config;


class App {
    /** @var App */
    protected static $instance;
    protected static $phpWarning = [
        E_WARNING,
        E_CORE_WARNING,
        E_COMPILE_WARNING,
        E_USER_WARNING,
        E_NOTICE,
        E_USER_NOTICE,
    ];
    protected static $loginToken;
    protected static $page;
    protected static $pages;
    protected static $defaultPage;
    protected static $responseType = Defines::APP_RESPONSE_TYPE_JSON;
    protected static $exportMode = false;


    /**
     * @var \A7\A7
     */
    public $container;

    protected function  __construct() {
    }

    /**
     * @return App
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new self();
            static::$instance->init();
        }
        return static::$instance;
    }
    public static function isLoggedUser($token) {

        $app = self::getInstance();
        /** @var \SITE\DAO\User $user */
        $user = $app->container->get('SITE\DAO\User');
        return $user->isLoggedUser($token);
    }

    protected function init() {
        $config = Config::getInstance();

        set_error_handler(['SITE\Helpers\App', 'errorHandler']);
        $this->container = new A7();
        $this->container->enablePostProcessor('DependencyInjection', $config->definition);
        $this->container->enablePostProcessor('Transaction', ['class' => '\SITE\Helpers\DB']);

    }
    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        if (Config::getInstance()->environment != 'development') return ;
        if ($errno == E_STRICT) return;
        $content = $errstr . "\n file " . $errfile . " line " . $errline;
        if (in_array($errno, self::$phpWarning)) {
            Notification::warning(1, $content, 'php_warning');
        } else {
            Notification::error(1, $content, 'php_error');
        }
    }
    public static function exceptionHandler(\Exception $exception) {
        if (Config::getInstance()->environment != 'development') return;
        $content = $exception->getMessage() . "\n file " . $exception->getFile() . " line " . $exception->getLine();
        Notification::error(1, $content, 'php_exception');
    }
    public static function setResponseType($responseType) {
        self::$responseType = $responseType;
    }
    public static function getLocale() {
        return "en_GB";
    }

    public static function getResponseType() {
        return self::$responseType;
    }

    public static function isExportMode() {
        return self::$exportMode;
    }

    public static function setExportMode($exportMode) {
        self::$exportMode = $exportMode;
    }
    public function checkArguments($arguments, $user){
        $res = true;
        if($user === false){
            $res = false;
        }

        foreach ($arguments as $key => $val) {
            switch ($key) {

            }
        }
        return $res;
    }

    
    protected function checkCall($methodName, &$arguments) {
        $user = false;
        unset($arguments['userId']);
        $availableFunctions = Defines::$availableFunctions;
        $res = true;
        if (!in_array($methodName, $availableFunctions)) {
            if (!isset($arguments['token'])) {
                $res = false;
            } else {
                $user = self::isLoggedUser($arguments['token']);
                if ($user === false) {
                    Notification::error(3, " You have been logged out because your account is logged in elsewhere.", 'SignIn');
                    $res = false;
                }
                $user =  $user['userId'];
                /** @var \SITE\DAO\User $user */
                unset($arguments['token']);
                $arguments['userId'] = $user;
            }
            if ($res !== false) {
                $res = $this->checkArguments($arguments, $user);
            }
        }
        return $res;
    }

    public function callFromRequest(array $arguments = []) {
        $classData = self::getClassNameAndMethodFromURI();
        if (empty($classData)) {
            throw new \Exception('empty class name or method name');
        }
        if ($classData == false) {
            return false;
        }
        $className = str_replace('-', '', $classData['className']);
        if(isset($arguments['command'])){
            $methodName = $arguments['command'];
            if(!isset($arguments['params'])){
                $arguments['params'] = [];
            }
            $arguments  = $arguments['params'];
        }else{
            $methodName = str_replace('-', '', $classData['methodName']);
        }

        if (empty($methodName)) {
            throw new \Exception('empty method name');
        }
        if (empty($className)) {
            throw new \Exception('empty class name');
        }

        $className = 'SITE\Services\\' . ucfirst($className) . 'Service';
        if(!$this->checkCall($methodName, $arguments)){
            return false;
        }
        
        return $this->call($className, $methodName, $arguments);
    }


    protected static function getClassNameAndMethodFromURI($apiPrefix = 'api/') {
        $retData = [];
        $requestURI = $_SERVER['REQUEST_URI'];
        $pos = strpos($requestURI, $apiPrefix);
        if ($pos !== false) {
            $requestURI = substr($requestURI, $pos + strlen($apiPrefix));
            $parsData = explode('/', $requestURI);
            if (count($parsData) > 0) {
                $retData['className'] = $parsData[0];
            }
            if (count($parsData) > 1) {
                $arr                  = $parsData = explode('?', $parsData[1]);
                $retData['methodName'] = $arr[0];
            }
            $parsData[1] = Utils::convertRequestServiceName($parsData[1]);


        }
        return $retData;
    }
    private function checkServiceArguments($className, &$arguments) {

        switch ($className) {
            case 'AFFPRO\Services\DataManagementService':
$a = 1;                return false;
                break;

        }

        return true;
    }

    public function call($className, $methodName, array $arguments = []) {
        $class = $this->container->get($className);
        if (A7::methodExists($class, $methodName)) {
            if (!$this->checkServiceArguments($className, $arguments)) {
                return false;
            }
            $reflectorMethod = ReflectionUtils::getInstance()->getMethodReflection($className, $methodName);
            foreach ($reflectorMethod->getParameters() as $param) {
                if (isset($arguments[$param->name])) {
                    $paramRefClass = $param->getClass();
                    if ($paramRefClass instanceof \ReflectionClass) {
                        $parentClass = $paramRefClass->getParentClass();
                        if ($parentClass && $parentClass->name == 'SITE\Helpers\Model') {
                            if (!$arguments[$param->name] instanceof Model) {
                                $arguments[$param->name] = new $paramRefClass->name($arguments[$param->name]);
                            }
                        } elseif ($paramRefClass->name == 'DateTime') {
                            if (!$arguments[$param->name] instanceof \DateTime) {
                                $arguments[$param->name] = new \DateTime($arguments[$param->name]);
                            }
                        }
                    }
                }
            }
        } else {
            throw new UndefinedMethodException();
        }
        return $this->container->call($class, $methodName, $arguments);
    }

}