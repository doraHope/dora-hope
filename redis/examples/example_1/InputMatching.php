<?php
namespace app\example_1;

use app\base\DataUtil;
use app\base\Log;
use app\base\Response;
use app\redis\RedisSortSet;

/**
 * 基于redis的使用实例, 根据用户输入返回匹配的内容
 * Class InputMatching
 * @package app\example_1
 */
class InputMatching
{

    private $sour;
    const MATCH_RESULT_LENGTH = 10;

    public function __construct()
    {
        $this->sour = new RedisSortSet('lexicon');
    }

    private function _insert($item, $score = 0)
    {
        return $this->sour->add($item, $score);
    }

    private function _rem($item)
    {
        return $this->sour->rem($item);
    }

    public function matching($input)
    {
        $log = new Log('/var/www/dora/log/redis/example1.log');
        $log->log('type 1 => '.$input);
        $startChr = '['.$input;
        $log->log('type 2 => '.$startChr);
        $endChr = '('.DataUtil::charMax($input);
        $log->log('type 3 => '.$endChr);
        $this->_insert($startChr, 0);
        $this->_insert($endChr, 0);
        $result = $this->sour->rangeByLex($startChr, $endChr, 0, 20);
        $this->_rem($startChr);
        $this->_rem($endChr);
        if(false !== $result) {
            Response::retJson(0, 'success', $result);
        }
        Response::retJson(1, 'fail');
    }


}