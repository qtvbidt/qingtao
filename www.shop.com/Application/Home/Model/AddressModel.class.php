<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/10
 * Time: 16:48
 */

namespace Home\Model;


use Think\Model;

class AddressModel extends Model{
    protected $patchValidate = true; //批量验证

    //自动验证
    protected $_validate = [
        ['name','require','收货人姓名不能为空'],
        ['province_id','require','省份不能为空'],
        ['city_id','require','市级城市不能为空'],
        ['area_id','require','区县不能为空'],
        ['detail_address','require','详细地址不能为空'],
        ['tel','require','手机不能为空'],
    ];
    public function addAddress(){
        $userinfo = login();//取出用户信息
        //如果勾选了默认地址
        if(isset($this->data['is_default'])){
            //先将其它的默认改为非默认，然后添加
            $this->where(['member_id'=>$userinfo['id']])->setField('is_default',0);
        }
        $this->data['member_id'] = $userinfo['id'];//加入用户id
        return $this->add();
    }

    //取出当前用户所有收货地址
    public function getList() {
        $userinfo = login();
        $row=$this->where(['member_id'=>$userinfo['id']])->select();
        return $row;
    }
    
    //获取指定的地址信息
    public function getAddressInfo($id,$field = '*'){
        $userinfo = login();//取用户信息
        $cond = [
            'member_id'=>$userinfo['id'],
            'id'=>$id,
        ];
        //指定的地址返回
        return $this->field($field)->where($cond)->find();
    }

    //修改数据保存
    public function saveddress($id){
        $userinfo = login();//取出用户信息
        //如果勾选了默认地址
        if(isset($this->data['is_default'])){
            //先将其它的默认改为非默认，然后添加
            $this->where(['member_id'=>$userinfo['id']])->setField('is_default',0);
        }
        $this->data['member_id'] = $userinfo['id'];//加入用户id
        $this->data['id'] = $id;//加入用户id
        //dump($this->data);exit;
        return $this->save();
    }
}