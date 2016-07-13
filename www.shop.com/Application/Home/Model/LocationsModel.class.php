<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/10
 * Time: 15:56
 */

namespace Home\Model;


use Think\Model;

class LocationsModel extends Model{
    //通过父级ID找他所有子类
    public function getListByParentId($parent_id=0){
        return $this->where(['parent_id'=>$parent_id])->select();
    }
}