<?php


namespace app\base;


class DataUtil
{

    public static function charMax($chr, $encode = false)
    {
        $length = strlen($chr);
        $ascii = ord($chr[$length-1])+1;
        //暂不处理最后一个字节符号ascii码值大于255的情况
        if($ascii >= 255) {
            return false;
        }
        $n_chr = chr($ascii);
        $chr[$length-1] = $n_chr;
        $ret = $chr;
        if($encode) {
            $retCode = iconv('utf-8', $encode, $chr);
            if($retCode) {
                $ret = $retCode;
            }
        }    
        return $ret;
    }

    public static function charMin($chr, $encode = false)
    {
        $length = strlen($chr);
        if($length <= 1) {
            $ret = $chr;
        } else {
            $ascii = ord($chr[$length-1])-1;
            //不考虑最后一个字节码小于0的情况
            if($ascii <= 0) {
                return false;
            }
            $n_chr = chr($ascii);
            $chr[$length-1] = $n_chr;
            $chr .= chr(255);
            $ret = $chr;
            if($encode) {
                $retCode = iconv('utf-8', $encode, $chr);
                if($retCode) {
                    $ret = $retCode;
                }
            }  
        }
        return $ret;
    }

}