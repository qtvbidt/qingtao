<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/13
 * Time: 23:04
 */

namespace Admin\Model;


use Think\Model;

class OrderInfoModel extends Model{
    //订单状态用于显示
    public $statuses = [
        0=>'已取消',
        1=>'待支付',
        2=>'待发货',
        3=>'待收货',
        4=>'完成',
    ];
    //获取所有订单的列表
    public function getList() {
        //根据创建时间降序查出所有订单
        return $this->order('inputtime desc')->select();
    }

}