<?php
namespace app\example_1;

use app\base\DataUtil;
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

    private function _score($item)
    {
        return $this->sour->score($item);
    }

    public function matching($input)
    {
        $startChr = DataUtil::charMin($input);
        $endChr = DataUtil::charMax($input);
        $this->_insert($startChr, 0);
        $this->_insert($endChr, 0);
        $scoreStart = $this->_score($startChr);
        $scoreEnd = $this->_score($endChr);
        $result = $this->sour->rangeByScore($scoreStart, $scoreEnd, [0, self::MATCH_RESULT_LENGTH]);
        if($result) {
            Response::retJson(0, 'success', $result);
        }
        Response::retJson(1, 'fail');

    }


}