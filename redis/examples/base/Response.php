<?php


namespace app\base;


class Response
{

    public static function retJson($code, $msg = '', $data = [], $withSlashes = false)
    {
        if($withSlashes) {
            return json_encode([
                'code' => $code,
                'msg' => $msg,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    }

}