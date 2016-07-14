<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/8
 * Time: 22:35
 */

namespace Home\Model;


use Think\Model;

class ShoppingCarModel extends Model{
    /**
     * 通过商品id获取购物车中指定商品的数量.
     * @param integer $goods_id 商品id.
     * @return integer
     */
    public function getAmountByGoodsId($goods_id) {
        $userinfo=login();
        $cond=[
            'member_id'=>$userinfo['id'],
            'goods_id'=>$goods_id,
        ];
        return $this->where($cond)->getField('amount');
    }


    /**
     * 将数据表中,指定的商品购买数量增加.
     * @param integer $goods_id 商品id.
     * @param integer $amount 商品的数量.
     * @return bool
     */
    public function addAmount($goods_id,$amount) {
        $userinfo=login();
        $cond=[
            'member_id'=>$userinfo['id'],
            'goods_id'=>$goods_id,
        ];
        return $this->where($cond)->setInc('amount',$amount);
    }

    /**
     * 将商品添加到数据库中.
     * @param integer $goods_id 商品id.
     * @param integer $amount 商品的数量.
     * @return bool
     */
    public function add2car($goods_id,$amount) {
        $userinfo=login();
        $data=[
            'member_id'=>$userinfo['id'],
            'goods_id'=>$goods_id,
            'amount'=>$amount,
        ];
        return $this->add($data);
    }

    /**
     * 把购物车cookie数据保存到数据表中
     * 1.cookie有数据，
     *  1.1.把数据库中当前用户的购物车中当前商品信息清空
     *  1.2.把cookie中的数据放入数据库
     * 2.cookie中没有数据，则不用管
     */
    public function cookie2db(){
        $userinfo=login(); //取出用户信息
        $key=C('SHOPPING_CAR_COOKIE_KEY');  //取出COOKIE数据
        $cookie_car=cookie($key);
        //如果COOKIE中没数据直接结束
        if(!$cookie_car){
            return true;
        }
        //如果有cookie数据才执行下面的代码
        $cond=[
            'member'=>$userinfo['id'],
            'goods_id'=>[
                'in',array_keys($cookie_car),
            ],
        ];
        //删除数据表中相同商品的信息
        if($this->where($cond)->delete()=== false){
            return false;
        }
        // 将cookie中商品信息保存到数据表
        $data = [];
        foreach ($cookie_car as $key => $value) {
            $data[] = [
                'goods_id' => $key,
                'amount' => $value,
                'member_id' => $userinfo['id'],
            ];
        }

        return $this->addAll($data);
    }


    /**
     * 获取购物车列表。区分是否登陆
     * 1.登陆状态
     *  1.1 从MySQL中获取商品的id和amount
     *  1.2 从goods表中获取商品的logo shop_price name
     *  1.3 准备完整的数据给前端
     * 2.未登录状态
     *  1.1 从cookie中获取商品的id和amount
     *  1.2 从goods表中获取商品的logo shop_price name
     *  1.3 准备完整的数据给前端
     */
    public function getShoppingCarList(){
        //判断是否登陆
        $userinfo=login();
        //获取商品的id和amount
        if($userinfo){
            //登陆情况
            $car_list = $this->where(['member_id' => $userinfo['id']])->getField('goods_id,amount');
        }else{ //没登录
            $car_list = cookie(C('SHOPPING_CAR_COOKIE_KEY'));
        }
//dump($this->getLastSql());exit;
        //获取出商品的详细信息
        //没有商品
        if(!$car_list){
            return [
                'total_price' => '0.00',
                'goods_info_list'=>[],
            ];
        }

        //有商品
        $goods_model = M('Goods');
        $cond        = [
            'id' => ['in', array_keys($car_list)],
            'is_on_sale'=>1,
            'status'=>1,
        ];
        $goods_info_list = $goods_model->where($cond)->getField('id,name,logo,shop_price');

        $total_price = 0.00; //总计
        //读取用户的积分
        $score = M('Member')->where(['id'=>$userinfo['id']])->getField('score');
        //获取用户的级别
        $cond = [
            'bottom'=>['elt',$score],
            'top'=>['egt',$score],
        ];
        $member_level = M('MemberLevel')->where($cond)->field('id,discount')->find();
        $member_level_id = $member_level['id'];//会员级别ID
        $discount = $member_level['discount'];// 会员的折扣率
        //获取用户的会员价
        $member_goods_price_model = M('MemberGoodsPrice');
        foreach($car_list as $goods_id=>$amount){
            //获取当前商品的会员价
            $cond = [
                'goods_id'=>$goods_id,
                'member_level_id'=>$member_level_id,
            ];
            $member_price = $member_goods_price_model->where($cond)->getField('price');
            if($member_price){

                //如果高了会员价就用会员价
                // 单价                                       这个函数是为了保留两位小数
                $goods_info_list[$goods_id]['shop_price'] = locate_number_format($member_price);
            }elseif($userinfo){

                //没设会员价用原价*折扣率
                $goods_info_list[$goods_id]['shop_price'] = locate_number_format($goods_info_list[$goods_id]['shop_price'] * $discount / 100);

            }else{
                //如果没有登陆，按照 原价显示
                $goods_info_list[$goods_id]['shop_price'] = locate_number_format($goods_info_list[$goods_id]['shop_price']);
            }


            $goods_info_list[$goods_id]['amount'] = $amount; //商品数量
            // 小计
            $goods_info_list[$goods_id]['sub_total'] = locate_number_format($goods_info_list[$goods_id]['shop_price'] * $amount);
            // 计算总计
            $total_price += $goods_info_list[$goods_id]['sub_total'];

        }
        //总计的小数保留设置
        $total_price = locate_number_format($total_price);
        //返回总计和数组
        return compact('total_price','goods_info_list');
    }


    //删除当前用户购物车数据
    public function clearShoppingCar() {
        $userinfo = login();
        return $this->where(['member_id'=>$userinfo['id']])->delete();
    }
}