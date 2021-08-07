<?php 

namespace App;

class URLHelper {

    public static function withParam(array $data, string $param, $value):string
    {
        if ( is_array($value) ){
            $value = implode(',', $value);
        }
        return http_build_query(array_merge($data, [$param => $value]));
    }

    public static function withParams(array $params, array $data)
    {
        return http_build_query(array_merge($data, $params));
    }
}