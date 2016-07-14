<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/13
 * Time: 23:01
 */

namespace Admin\Controller;


use Think\Controller;

class OrderInfoController extends Controller{
    //获取所有订单的列表  有时间打它分页显示
    public function index(){
        $order_info_model = D('OrderInfo');
        //所有订单
        $rows = $order_info_model->getList();
        $this->assign('rows', $rows);
        //状态显示用
        $this->assign('statuses', $order_info_model->statuses);
        $this->display();
    }

    //填发货信处
    public function send($id) {
        if(IS_POST){
            $order_info_model = D('OrderInfo');
            //订单状态改为3
            if ($order_info_model->where(['id' => I('id')])->setField('status', 3) === false) {
                $this->error(get_error($order_info_model));
            } else {
                //完成后跳回订单展示
                $this->success('发货成功', U('index'));
            }
        }else{
            $this->assign('id', $id);
            $this->display();
        }

    }

    //关于订单和库存的修改
    public function clearTimeOutOrder(){
        M()->startTrans();//开事务
        //获取超时订单
        $order_info_model = D('OrderInfo');
        //获取所有超时（创建订单15分钟以上）的订单id
        $order_ids = $order_info_model->where(['intputtime' => ['lt', NOW_TIME - 900], 'status' => 1])->getField('id', true);
        //如果没有超时的那么后面的不执行直接返回
        if (!$order_ids) {
            return true;
        }
        //把这些订单的状态全都改为0
        $order_info_model->where(['id' => ['in', $order_ids]])->setField('status', 0);
        //恢复库存
        //先获取超时订单中各商品的数量
        $order_info_item_model = M('OrderInfoItem');
        //所有超时订单包含商品的id和数时得到
        $goods_list = $order_info_item_model->where(['id' => ['in', $order_ids]])->getField('id,goods_id,amount');
        //接下来把这些数量统计起来在商品表中把库存加起来
        $goods_model = M('Goods');
        $data = [];
        foreach ($goods_list as $goods) {
            //如果不同订单中出现相同商品
            if (isset($data[$goods['goods_id']])) {
                $data[$goods['goods_id']] += $goods['amount'];
            } else {
                //所有超时订单第一次出现该商品
                $data[$goods['goods_id']] = $goods['amount'];
            }
        }
        //把所有超时订单相关的商品表中库存加上订单中该商品数量的总和
        foreach ($data as $goods_id => $amount) {
            //setInc('stock',$amount)把'stock'字段中都加上数字$amount作为新数据
            $goods_model->where(['id'=>$goods_id])->setInc('stock',$amount);
        }
        M()->commit();//事务提交
    }

}