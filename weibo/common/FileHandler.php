<?php


namespace app\common;


class FileHandler
{

    public static function verifyUploadFileSize($file)
    {
        if($file['size'] < FILE_SIZE_MIN || $file['size'] > FILE_SIZE_MAX) {
            return false;
        }
    }

    public static function verifyUploadFileType($ext)
    {
        if(!in_array(\WeiBoConfig::$CAN_UPLOAD_FILE_TYPE, $ext)) {
            return false;
        }
        return true;
    }

    /**
     * @param $uid
     * @param $dirPrefix
     * @param $file
     * @return string   上传文件保存的路径
     */
    public static function pathOfUpload($uid, $dirPrefix, $file)
    {
        return [
            WB_MESSAGE_SAVE_PATH.($uid%WB_FILE_DIR_MOD_NUMBER).$dirPrefix.DIRECTORY_SEPARATOR.$file,    //文件存储路径
            ($uid%WB_FILE_DIR_MOD_NUMBER).$dirPrefix.DIRECTORY_SEPARATOR.$file                          //用于数据库存储
        ];
    }

    public static function moveUploadFile($uid, $files)
    {
        $success = [];
        $fail = [];
        foreach ($files as $file) {
            if(self::verifyUploadFileSize($file)) {
                $fileExt = substr($file['tmp_name'], strpos($file['tmp_name'], '.')+1);
                if(self::verifyUploadFileType($fileExt)) {
                    $fail[] = $file['tmp_name'];
                    continue;
                }
                $filePrefix = WBCommon::hash($file['tmp_name']);
                $retFile = self::pathOfUpload($uid, $filePrefix.'.'.$fileExt);
                if(move_uploaded_file($file['tmp_name'], $retFile[1])) {

                }
                $success[] = $retFile[0];
            }
        }
        return [
            'success' => $success,
            'fail' => $fail
        ];
    }

    public static function exists($fileName)
    {
        if(file_exists($fileName)) {
            return false;
        } else {
            return true;
        }
    }

    public static function verifyFile()
    {

    }

}