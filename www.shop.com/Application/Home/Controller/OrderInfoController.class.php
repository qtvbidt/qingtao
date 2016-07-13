<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/12
 * Time: 13:47
 */

namespace Home\Controller;


use Think\Controller;

class OrderInfoController extends Controller{
    /**
     * @var \Home\Model\OrderInfoModel
     */
    private $_model = null;

    protected function _initialize() {
        $this->_model = D('OrderInfo');
    }

    //创建订单
    public function add(){
        if(IS_POST){
            //接收数据
            if($this->_model->create() === false){
                $this->error(get_error($this->_model));
            }
            //创建订单
            if($this->_model->addOrder() === false){
                $this->error(get_error($this->_model));
            }
            $this->success('创建订单成功',U('Cart/flow3'));
        }else{
            $this->error('拒绝直接访问');
        }
    }
    
    //展示用户订单列表
    public function index() {

    }

}