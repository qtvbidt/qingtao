<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/12
 * Time: 13:51
 */

namespace Home\Model;


use Think\Model;

class OrderInfoModel extends Model{
    //订单状态把数字变为字符
    public $statuses = [
        0=>'已取消',
        1=>'待支付',
        2=>'待发货',
        3=>'待收货',
        4=>'完成',
    ];


    /**
     * 创建订单
     * 1.创建订单基本信息表记录
     * 2.保存订单详情
     * 3.保存发票信息
     * 4.扣除库存
     *  获取每一个要购买的商品库存是否足够
     */
    public function addOrder(){
        $this->startTrans();//开启事务
        //1.保存订单基本信息
        //收货地址信息
        $address_model = D('Address');
        $address_info = $address_model->getAddressInfo(I('post.address_id'), 'province_name,city_name,area_name,tel,name,detail_address,member_id');
        //array_merge()合并数组的函数
        $this->data = array_merge($this->data, $address_info);

        //获取配送方式
        $delivery_model = D('Delivery');
        $delivery_info = $delivery_model->getDeliveryInfo(I('post.delivery_id'), 'name as delivery_name,price as delivery_price');
        $this->data = array_merge($this->data, $delivery_info);

        //支付方式
        $payment_model = D('Payment');
        $payment_info = $payment_model->getPaymentInfo(I('post.pay_type_id'), 'name as pay_type_name');
        $this->data = array_merge($this->data, $payment_info);

        //获取订单金额，从购物车中得到
        $shopping_car_model = D('ShoppingCar');
        $cart_info = $shopping_car_model->getShoppingCarList();

        //验证购物车中的数商品是否库存都够
        //dump($cart_info);exit;
        $cond['_logic']='OR';
        foreach($cart_info['goods_info_list'] as $key=>$value){
            $cond[] = [
                'id'=>$key,
                'stock'=>['lt',$value['amount'],],
            ];
        }
        $goods_model = M('Goods');//建立商品表模型
        //查询有没有库存量小于购物数量的
        $not_enough_stock_list = $goods_model->where($cond)->select();
        //dump($not_enough_stock_list);exit;
        $error = '';
        //如果有就说明库存不足，不创建订单 后面代码不执行
        if($not_enough_stock_list){
            foreach($not_enough_stock_list as $goods){
                $error .= $goods['name'] . ',';
            }
            $this->error = $error . '库存不足';//拼接错误提示，明确是哪个商品库存不足
            //dump($error);exit;
            $this->rollback();//回滚事务
            return false;
        }

        //如果库存够，那么要将数据库中对应商品的库存量减云订单中商品的数量
        foreach($cart_info['goods_info_list'] as $goods){
            //setDec('字段名a','数字b')将表中对应字段a中的数字减云b成为新数据
            if($goods_model->where(['id'=>$goods['id']])->setDec('stock', $goods['amount'])===false){
                $this->error = '更新库存失败';
                $this->rollback();
                return false;
            }
        }

        $this->data['price'] = $cart_info['total_price'];//订单总金额
        $this->data['status'] = 1; //订单创建状态为未支付
        //保存订单基本信息
        if (($order_id = $this->add()) === false) {
            $this->rollback(); //回滚事务
            return false;
        }

        //2.订单详情
        //购物车数据
        $data = []; //保存插入数据的数组
        foreach ($cart_info['goods_info_list'] as $goods) {
            $data[] = [
                'order_info_id' => $order_id,
                'goods_id' => $goods['id'],
                'goods_name' => $goods['name'],
                'logo' => $goods['logo'],
                'price' => $goods['shop_price'],
                'amount' => $goods['amount'],
                'total_price' => $goods['sub_total'],
            ];
        }
        //保存订单详情到数据表
        $order_info_item_model = M('OrderInfoItem');
        if ($order_info_item_model->addAll($data) === false) {
            $this->error = '保存订单详情失败';
            $this->rollback();
            return false;
        }
        
        //3.发票信息
        //获取抬头类型，个人还是公司
        $receipt_type = I('post.receipt_type');
        if ($receipt_type == 1) {
            //如果是个人 名字就是收货地址中的名字
            $receipt_title = $address_info['name'];
        } else {
            //如果是公司，名字传过来
            $receipt_title = I('post.company_name');
        }
        
        //拼接发票内容
        $receipt_content_type = I('post.receipt_content_type');//发票内容选什么
        $receipt_content = '';
        switch ($receipt_content_type) {
            case 1:
                $tmp = [];
                foreach ($cart_info['goods_info_list'] as $goods) {
                    $tmp[] = $goods['name'] . ":" . $goods['shop_price'] . '×' . $goods['amount'] . "=" . $goods['sub_total'];
                }
                $receipt_content = implode("\r\n", $tmp);
                break;
            case 2:
                $receipt_content .= '办公用品';
                break;
            case 3:
                $receipt_content .= '体育休闲';
                break;
            default:
                $receipt_content .= '耗材';
                break;
        }
        $content = $receipt_title . "\r\n" . $receipt_content . "\r\n总计：" . $cart_info['total_price'];
        $data = [  // 准备插入的数据
            'name' => $receipt_title,
            'content' => $content,
            'price' => $cart_info['total_price'],
            'inputtime' => NOW_TIME,
            'member_id' => $address_info['member_id'],
            'order_info_id' => $order_id,
        ];

        //保存订单的发票信息
        if (M('Invoice')->add($data) === false) {
            $this->error = '保存发票失败';
            $this->rollback();
            return false;
        }

        //清空购物车
        if($shopping_car_model->clearShoppingCar()===false){
            $this->error = '清空购物车失败';
            $this->rollback();
            return false;
        }

        $this->commit(); //提交事务
        return true;
    }

    //获取用户的订单列表
    public function getList() {
        $userinfo = login();//获取用户信息
        $cond = [
            'member_id'=>$userinfo['id'],
        ];
        $rows = $this->where($cond)->select();//查出当前用户的所有订单
        //取出订单详情
        //创详情表模型
        $order_info_item_model = M('OrderInfoItem');
        //取出对应商品的详情放入数组中
        foreach($rows as $key=>$value){
            $rows[$key]['goods_list'] = $order_info_item_model->field('goods_id,goods_name,logo')->where(['order_info_id'=>$value['id']])->select();
        }
        return $rows;
    }

    //通过订单id获取订单详情
    public function getOrderInfoById($id) {
        return $this->find($id);
    }
}