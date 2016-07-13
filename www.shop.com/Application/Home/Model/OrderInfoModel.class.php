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
    /**
     * 创建订单
     * 1.创建订单基本信息表记录
     * 2.保存订单详情
     * 3.保存发票信息
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
}