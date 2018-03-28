<?php
namespace SITE\Models;


use SITE\Helpers\App;
use SITE\Helpers\Notification;

class AjaxRequest {
    protected static $response = null;

    protected $arguments = [];

    /**
     * @return AjaxRequest
     */
    public static function getInstance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }

    protected function __construct() {
        $postData = file_get_contents("php://input");
        if($postData) {
            $json = json_decode($postData, true);
            if(isset($json)) {
                $this->arguments = $json;
                /*foreach ($json as $key => $val) {
                    if (property_exists($this, $key)) {
                        $this->{$key} = $val;
                    }
                }
                */
            }
        }
        $this->arguments += $_GET;
    }


    public function getResponse() {
        if (self::$response === null) {
            self::$response = $this->initResponse();
        }
        return self::$response;
    }

    /**
     * @return AjaxResponse
     */
    protected function initResponse() {
        $ajaxResponse = new AjaxResponse();
        try {
            $app = App::getInstance();
            $ajaxResponse->result = $app->callFromRequest($this->arguments);
        } catch (\Exception $e) {
            $ajaxResponse->status = 'internalError';
           // $ajaxResponse->result = new \stdClass();
            App::exceptionHandler($e);
        }
        $ajaxResponse->notification = Notification::getAll();
        return $ajaxResponse;
    }

    private function __clone() {}

    private function __wakeup() {}

}