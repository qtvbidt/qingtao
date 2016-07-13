<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/11
 * Time: 18:04
 */

namespace Home\Model;


use Think\Model;

class DeliveryModel extends Model{
    //取出所有可用的送货方式
    public function getList() {
        return $this->where(['status'=>1])->order('sort')->select();
    }

    /**
     * 获取指定的配送方式信息。
     * @param integer $id 地址id。
     * @param string  $field 要读取的字段列表。
     * @return array|null
     */
    public function getDeliveryInfo($id,$field = '*') {
        $cond = [
            'id'=>$id,
        ];
        return $this->field($field)->where($cond)->find();
    }
}