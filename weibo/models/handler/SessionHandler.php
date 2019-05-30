<?php

namespace app\models\handler;

class SessionHandler
{
    private $handler;
    private $timestamp;
    private $param = [];
    private $sessionName;

    /** session重写初始化方法
     *
     */

    public static function init()
    {
        $handler = new self;
        session_set_save_handler(
            [$handler, 'open'],
            [$handler, 'close'],
            [$handler, 'read'],
            [$handler, 'write'],
            [$handler, 'destroy'],
            [$handler, 'gc']
        );
        register_shutdown_function('session_write_close');
    }

    public function open($savePath, $sessionName)
    {
        //仅对$handler 赋值
        $this->timestamp = time();
        $this->handler = \Yii::$app->get('redis');
        return true;
    }

    public function close()
    {
        //写入redis
        $session = serialize($this->param);
        $this->handler->stringSet($this->sessionName, $session, 86400);
        return true;
    }

    public function read($sessionId)
    {
        if(!$this->sessionName) {
            $this->sessionName = 'sess_'.$sessionId;
            if(!($oldData = $this->handler->stringGet($this->sessionName))) {
                $this->param['timestamp'] = time();
                $this->handler->stringSet($this->sessionName, serialize($this->param), 86400);
            } else {
                $this->param = unserialize($oldData);
            }
        }
        return serialize($this->param);
    }

    public function write($sessionId, $data)
    {
        $this->param = unserialize($data);
        return true;
    }

    public function destroy($sessionId)
    {
        $this->param = [];
        $this->handler->delKey('sess_'.$sessionId);
        return true;
    }

    public function gc($lifetime)
    {
        if ($this->timestamp + $lifetime <= time()) {
            $this->param = [];
        }
        return true;

    }

}