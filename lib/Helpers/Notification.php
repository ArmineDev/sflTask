<?php


namespace SITE\Helpers;


class Notification {

    const STATUS_ERROR   = 'error';
    const STATUS_WARNING = 'warning';
    const STATUS_SUCCESS = 'success';
    const STATUS_INFO    = 'info';

    protected static $noteList = [];

    public static function info($code, $content, $title='') {
        self::note($code, $content, $title, self::STATUS_INFO);
    }
    public static function error($code, $content, $title='') {
        self::note($code, $content, $title, self::STATUS_ERROR);
    }
    public static function warning($code, $content, $title='') {
        self::note($code, $content, $title, self::STATUS_WARNING);
    }
    public static function success($code, $content, $title='') {
        self::note($code, $content, $title, self::STATUS_SUCCESS);
    }
    public static function getAll() {
        return self::$noteList;
    }

    protected static function note($code, $content, $title = '', $status = self::STATUS_INFO) {
        self::$noteList[] = [
            'code'    => $code,
            'content' => $content,
            'title'   => $title,
            'status'  => $status,
        ];
    }
}