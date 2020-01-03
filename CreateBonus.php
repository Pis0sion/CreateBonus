<?php
/**
 * Created by PhpStorm.
 * User: zihua
 * Date: 2019/3/3
 * Time: 13:02
 */

namespace app\api\service;

/**
 * 裂变红包
 * Class CreateBonus
 * @package app\api\service
 */
class CreateBonus
{
    private $total;

    private $count;

    private $min;

    private $max;
    
    public function test()
    {
        return "this is a test html" ;
    }

    
    //  设置总金额
    public function setTotal($total)
    {
        $this->total = $total * 100;
    }

    //  设置红包个数
    public function setBonusNum($num)
    {
        $this->count = $num;
    }

    //   设置最小的金额
    public function setBonusMin($min)
    {
        $this->min = $min * 100 ;
    }

    //   设置最大金额
    public function setBonusMax($diff)
    {
        $this->max = $this->total / $this->count + $diff;
    }
    //   获取详细的红包列表
    public function getBonusPackage()
    {
        $pack = [];
        $result_bonus = $this->getBonus($this->total,$this->count,$this->max,$this->min);
        foreach ($result_bonus as &$bonus){
            $pack[] = $bonus/100 ;
        }
        return $pack;
    }

    /**
     * 生产min和max之间的随机数，但是概率不是平均的，从min到max方向概率逐渐加大。
     * 先平方，然后产生一个平方值范围内的随机数，再开方，这样就产生了一种“膨胀”再“收缩”的效果。
     */
    private function xRandom($bonus_min, $bonus_max)
    {
        $sqr = intval($this->sqr($bonus_max - $bonus_min));
        $rand_num = rand(0, ($sqr - 1));
        return intval(sqrt($rand_num));
    }

    // 求平方
    private function sqr($n)
    {
        return $n * $n;
    }

    /**
     * 获取红包列表
     * @param $bonus_total
     * @param $bonus_count
     * @param $bonus_max
     * @param $bonus_min
     * @return array
     */
    private function getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min)
    {
        $result = array();
        $average = $bonus_total / $bonus_count;

        $a = $average - $bonus_min;
        $b = $bonus_max - $bonus_min;


        //
        //这样的随机数的概率实际改变了，产生大数的可能性要比产生小数的概率要小。
        //这样就实现了大部分红包的值在平均数附近。大红包和小红包比较少。
        $range1 = $this->sqr($average - $bonus_min);
        $range2 = $this->sqr($bonus_max - $average);


        for ($i = 0; $i < $bonus_count; $i++) {
            //因为小红包的数量通常是要比大红包的数量要多的，因为这里的概率要调换过来。
            //当随机数>平均值，则产生小红包
            //当随机数<平均值，则产生大红包
            if (rand($bonus_min, $bonus_max) > $average) {
                // 在平均线上减钱
                $temp = $bonus_min + $this->xRandom($bonus_min, $average);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            } else {
                // 在平均线上加钱
                $temp = $bonus_max - $this->xRandom($average, $bonus_max);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            }
        }
        // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
        while ($bonus_total > 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total > 0 && $result[$i] < $bonus_max) {
                    $result[$i]++;
                    $bonus_total--;
                }
            }
        }
        // 如果钱是负数了，还得从已生成的小红包中抽取回来
        while ($bonus_total < 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total < 0 && $result[$i] > $bonus_min) {
                    $result[$i]--;
                    $bonus_total++;
                }
            }
        }
        return $result;
    }

}
