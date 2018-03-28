<?php

namespace SITE\Helpers;


use SITE\Exceptions\ConfigurationException;
/**
 * Class Config
 * @property array $definition
 * @property string $superPassword
 * @property string $environment


 */

class Config {
    use GetterSetter;

    /** @var Config */
    protected static $instance = null;
    protected $apiUrl = '';
    protected $definition = [];
    protected static $rootDir ;
    protected $superPassword;
    protected $environment = 'development';





    /**
     * @return Config
     * @throws ConfigurationException
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            throw new ConfigurationException('Configuration not initialized');
        }
        return static::$instance;
    }

    public function getEnvironment() {
        return $this->environment;
    }
    public function getApiUrl() {
        return $this->apiUrl;
    }

    protected function __construct($config) {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function getDefinition() {
        if (!is_array($this->definition)) {
            $this->definition = [];
        }
        return $this->definition;
    }
    public function getSuperPassword() {
        return $this->superPassword;
    }




    public static function getRootDir() {
        return self::$rootDir;
    }


    public static function init() {
        self::$rootDir = dirname(dirname(__DIR__));

        $globalConfigPath = self::$rootDir . DIRECTORY_SEPARATOR . 'config.php';
        $config = [];
        if (file_exists($globalConfigPath)) {
            $config = require $globalConfigPath;
            if (!is_array($config)) {
                $config = [];
            }
        }

        static::$instance = new static($config);
        App::getInstance();

    }

}