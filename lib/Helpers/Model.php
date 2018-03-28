<?php


namespace SITE\Helpers;


abstract class Model {

    public function __construct($data = []) {
      //  var_dump($data);die;
        if(!empty($data)) {
            foreach ($data as $key => $val) {
                $setter = 'set' . $key;
                if (method_exists($this, $setter)) {
                    $this->$setter($val);
                } else if (property_exists($this, $key)) {
                    $this->{$key} = $val;
                }
            }
        }
    }

    public function toArray() {
        return get_object_vars($this);
    }

}