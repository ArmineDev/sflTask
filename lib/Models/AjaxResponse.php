<?php
namespace SITE\Models;


use SITE\Helpers\App;
use SITE\Helpers\Defines;
use SITE\Helpers\GetterSetter;

/**
 * Class AjaxResponse
 * @package AFF\Models
 *
 * @property boolean $status
 * @property mixed $result
 * @property array $notification
 */
class AjaxResponse {
    use GetterSetter;

    protected $status = "OK";
    protected $result = null;
    protected $notification = [];



    /**
     * @param mixed $result
     */
    public function setResult($result){
        if (!is_bool($result) && (empty($result) || is_null($result))) {
            $result = new \stdClass();
        } else {
            $this->status = "OK";
            if ($result === false ) {
                $result = new \stdClass();
                $this->status = "error";
            } elseif ($result === true) {
                $result = new \stdClass();
            }elseif(!is_object($result)){
                if(is_array($result)){
                    $result = (object)$result;
                }else {
                    $result = new \stdClass();
                    $this->status = "internalError";
                }
            }
        }
        $this->result = $result;
    }
    public function printResponse() {
        switch(App::getResponseType()) {
            case Defines::APP_RESPONSE_TYPE_JSON :
                $this->printJSON();
                break;
            default :
                echo "other";
                break;

        }
    }
    /**
     * @param boolean $status
     */
    public function setStatus($status) {
        if($status === "internalError"){
            $this->result = new \stdClass();
        }
        $this->status = $status;
    }

    /**
     * @param array $notification
     */
    public function setNotification($notification) {
       // if($this->status !== "internalError") {
            $this->notification = $notification;
        //}
    }




    public function printJSON() {
        header('Content-Type: application/json');
        echo json_encode(get_object_vars($this), JSON_UNESCAPED_UNICODE);
    }


}