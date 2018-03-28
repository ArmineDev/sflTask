<?php
namespace SITE\Helpers;

class Template
{
    private static $globalTemplateDir;
    private static $templateDir;



    private static function dir(){
        if (!isset(self::$templateDir)) {
            self::$templateDir = DIRECTORY_SEPARATOR . 'templates'  . DIRECTORY_SEPARATOR;

            self::$globalTemplateDir = Config::getRootDir() . self::$templateDir;
            if (!file_exists(self::$globalTemplateDir)) {
                die('Template  Not found!');
            }
        }
        return self::$globalTemplateDir;
    }
    private function header() {
        self::file('pages' . DIRECTORY_SEPARATOR.'header.php');
    }
    private function headerLogin() {
        self::file('pages' . DIRECTORY_SEPARATOR.'headerLogin.php');
    }

    public static function init(){
        $res = true;
        if (isset($_GET['token']) && App::isLoggedUser($_GET['token']) === true) {
            self::headerLogin();
            if (isset($_GET['page'])) {
                $res = self::page($_GET['page']);
            } else
                self::page('defaultLogin');
        }else{
            self::header();
            self::page('default');
        }
        if($res == false){
            self::page('default');

        }
    }


    public static function page($page){
        return self::file('pages' . DIRECTORY_SEPARATOR . $page . '.php');
    }

    private static function file($file){
        $globalPath = self::dir() . $file;
        if (file_exists($globalPath)) {
            require_once $globalPath;
        } else {
            Notification::error(1, 'file "' . $file . '" Not found! ', 'Template');
            return false;
        }
    }


}
