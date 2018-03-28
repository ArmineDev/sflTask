<?php

namespace SITE\Helpers;

use SITE\Helpers\Defines as D;


use DateInterval;
use DatePeriod;
use DateTime;

class Utils {

    public static function arrayColumn($inputArray, $index) {
        if (function_exists('array_column')) {
            return array_column($inputArray, $index);
        } else {
            $outputArray = [];
            foreach ($inputArray as $inputArrayRow) {
                $outputArray[] = $inputArrayRow[$index];
            }
            return $outputArray;
        }
    }
    public static function checkAfterCommaNumbers($number,$numberAfterComma){
        return (strlen(substr(strrchr($number, "."), $numberAfterComma)) > 1);
    }

    public  static  function isValidTimeStamp($timestamp){
        return true;
       /* return ((string) (int) $timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);*/
    }
     public  static function getArraySecondLevelKeys($array){
        $result = array();
        foreach(array_values($array) as $sub) {
            $result = array_merge($result, $sub);
        }
        return $result;
    }

    public static function initMonthDate($from = null, $to = null) {
        if (!isset($from) || !isset($to)) {
            $from = new \DateTime('first day of this month');
            $from->setTime(0, 0, 0);
            $to = new \DateTime();
            /*$from->setDate($from->format('Y'), 4, $from->format('d'));
            $to->setDate($to->format('Y'), 4, 30);
            $to->setTime(23,59,59);*/
        }
        //TODO: remove tgi liner@
        $from->setTime(0, 0, 0);
        $to->setTime(23, 59, 59);

        return [$from, $to];
    }




    public static function truncate_number($number, $precision = 2) {
        $negative = 1;
        if ($number != 0) {
            $negative = $number / abs($number);
        }
        $number = abs($number);
        $precision = pow(10, $precision);
        $res = floor($number * $precision) / $precision;
        if($res != 0 ){
            $res = $res * $negative;
        }
        return $res;
    }

    public static  function limit(&$query, $start = 0, $limit = 10){
        $start = abs((int)$start);
        $limit = (int)$limit;
        if($limit && $limit != "-1") {
            $query .= " LIMIT {$start}, {$limit}";
        }

    }



    public static function roundAllNumeric($arr) {
        foreach ($arr as &$val) {
            if ($val == '0') $val = 0;
            if (is_numeric($val) && !empty($val)) {
                $val = self::truncate_number($val);

            }
        }
        return $arr;
    }

    public static function isValidFloat($string) {
        if (!preg_match("/^[-+]?[0-9]+(.[0-9]+)?$/", $string)) {
            return false;
        }
        return true;
    }


    public static function dayByDayRecordsAndTotal(array $res, array $emptyRow, $fromDate,  $toDate, $genRecords = false) {

        $total = $emptyRow;
        $records = [];
        $begin = $fromDate;
        $end = $toDate;
        if(is_string($fromDate) && is_string($toDate)){
            $begin = new DateTime( $fromDate );
            $end = new DateTime( $toDate );
        }

        if($genRecords) {
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);
            foreach ($period as $cDate) {
                /** @var \DateTime $cDate */
                $key = $cDate->format("Y-m-d");
                $records[$key] = $emptyRow;
            }
            foreach ($res as $row) {
                $records[$row['date']] = array_merge($records[$row['date']], Utils::roundAllNumeric($row));
            }
        }
        $keys = array_keys($emptyRow);
        foreach ($res as $row) {
            foreach ($keys as $key) {
                $total[$key] += $row[$key];
            }
        }
        return [
            'records' => array_values($records),
            'total'   => $total,
        ];
    }

    public static function moveFiles($fromPath, $toPath, $except = []) {
        if (!file_exists($fromPath)) {
            return false;
        }

        if (is_dir($fromPath)) {
            $toPath = self::createDirectory($toPath);
        }

        foreach (glob($fromPath . "/*") as $file) {
            $basename = basename($file);

            if (in_array($basename, $except)) {
                continue;
            }

            if (is_dir($file)) {
                self::moveFiles($file, $toPath . basename($file) . DIRECTORY_SEPARATOR);
            } elseif (is_file($file)) {
                copy($file, $toPath . basename($file));
            }
            elseif(is_file($file)) {
                copy($file, $toPath.basename($file));
            }
        }

    }

    public static function createDirectory($directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

    public static function deleteDirectory($directory, $selfDelete) {
        $files = glob($directory . "/*");

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
            if (is_dir($file)) {
                self::deleteDirectory($file, true);
            }
        }

        if ($selfDelete == true && count(glob($directory . "/*")) == 0) {
            rmdir($directory);
        }

        return true;
    }

    public static function arrayToAssoc($array, $key) {
        $assocArray = [];
        foreach ($array as $arrayRow) {
            if (isset($arrayRow[$key])) {
                $assocArray[$arrayRow[$key]] = $arrayRow;
            } else {
                return [];
            }

        }

        return $assocArray;
    }


    public static function isAssoc(array $arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function consoleColorTest($string, $color) {
        $colors = [
            'black'        => '0;30m',
            'dark_gray'    => '1;30m',
            'blue'         => '0;34m',
            'light_blue'   => '1;34m',
            'green'        => '0;32m',
            'light_green'  => '1;32m',
            'cyan'         => '0;36m',
            'light_cyan'   => '1;36m',
            'red'          => '0;31m',
            'light_red'    => '1;31m',
            'purple'       => '0;35m',
            'light_purple' => '1;35m',
            'brown'        => '0;33m',
            'yellow'       => '1;33m',
            'light_gray'   => '0;37m',
            'white'        => '1;37m',
        ];
        $code = isset($colors[$color]) ? $colors[$color] : '1;37m';

        return "\033[".$code.$string."\033[0m";
    }

    public static function clearCdata($arr) {
        $resArr = [];
        foreach ($arr as $k => $v) {
            if(is_array($v) && count($v) == 1 && array_key_exists('@cdata', $v)) {
                $v = $v['@cdata'];
            }
            $resArr[$k] = $v;
        }
        return $resArr;
    }

    /**
     * Converts string to lowercase without non-word characters
     * @param $serviceName
     * @return string
     */
    public static function convertRequestServiceName($serviceName) {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $serviceName));
    }



    public static function generateRandomUniqueString($prefixCount = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $prefixCount; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString.uniqid();
    }

    public static function dynamicFilterInitialization(array $filterObj, array $maps = []) {
        // TODO
        $where = [];
        $bind = [];
        foreach ($filterObj as $key => $obj) {
            if (!isset($maps[$key]))
                continue;
            $field = $maps[$key];
            $key = 'f_' . $key;
            $value = $obj;
            $type = "=";
            $where[] = $field . ' ' . $type . ' :' . $key;
            $bind[$key] = trim($value);
        }
        return [$where, $bind];
    }

}