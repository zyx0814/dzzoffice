<?php
namespace  core\dzz;

class Datareturn{

    private static $returnData = '';

    private static $template = '';

    public static function data_return($type='json',$data='',$template='')
    {
        self::$returnData = $data;

        self::$template = $template;

        switch($type){

            case 'json':

                self::json_return();
                break;
            case 'string':

                self::string_return();

                break;

            case 'html':

                self::html_return();

                break;

            default:
                self::json_return();
        }
        exit;
    }

    private static function json_return()
    {

       echo   json_encode(self::$returnData);
        exit;
    }

    private static function html_return(){

        extract(self::$returnData);

        include template(self::$template);

        exit();
    }

    private static function string_return(){

        echo self::$returnData;
    }
}