<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/11
 * Time: 18:05
 */

namespace Home\Model;


use Think\Model;

class PaymentModel extends Model{
    //获取所有可以用的支付方式
    public function getList() {
        return $this->where(['status'=>1])->order('sort')->select();
    }


    /**
     * 获取指定的支付方式信息。
     * @param integer $id 地址id。
     * @param string  $field 要读取的字段列表。
     * @return array|null
     */
    public function getPaymentInfo($id,$field = '*') {
        $cond = [
            'id'=>$id,
        ];
        return $this->field($field)->where($cond)->find();
    }

}