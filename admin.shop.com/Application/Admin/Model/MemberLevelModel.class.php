<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/12
 * Time: 15:28
 */

namespace Admin\Model;


use Think\Model;

class MemberLevelModel extends Model{
    /**
     * 获取所有可用的会员等级
     * @return mixed
     */
    public function getList() {
        return $this->where(['status'=>1])->order('sort')->select();
    }
}