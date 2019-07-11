<?php


namespace app\base;


use mysql_xdevapi\Exception;

class Log
{

    private $fin;

    public function __construct($path)
    {
        if(!is_dir(dirname($path))) {
            if(@mkdir(dirname($path))) {
                throw new Exception(sprintf('目录%s创建失败', dirname($path)));
            }
        }
        $this->fin = fopen($path, 'a+');
        if(!$this->fin && !is_resource($this->fin)) {
            throw new Exception(sprintf('文件流%s打开失败', $path));
        }
    }

    public function log($content)
    {
        fwrite($this->fin, json_encode([
            'date' => date('Y-m-d H:i:s', time()),
            'content' => $content
        ], JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    public function close()
    {
        if($this->fin && is_resource($this->fin)) {
            fclose($this->fin);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

}