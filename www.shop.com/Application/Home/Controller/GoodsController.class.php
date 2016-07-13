<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/8
 * Time: 18:16
 */

namespace Home\Controller;


use Think\Controller;

class GoodsController extends Controller{
    /**
     * 获取商品点击次数.
     * @param integer $id 商品id
     */
    public function clickTimes($id){
        //echo $id;exit;
        $goods_click_model = M('GoodsClick');
        //获取历史点击次数
        $num = $goods_click_model->getFieldByGoodsId($id,'click_times');

        //存新的点击数到数据库
        if(!$num){  //如果数据库没有当前商品的点击次数说明是第一次点
            $num=1;
            $data=[
                'goods_id'=>$id,
                'click_times'=>$num,
            ];
            $goods_click_model->add($data);
        }else{
            ++$num;
            $data=[
                'goods_id'=>$id,
                'click_times'=>$num,
            ];
            $goods_click_model->save($data);
        }
        //dump($num);exit;
        $this->ajaxReturn($num); // 返回点击的次数
    }

    /**
     * 从redis中获取商品的点击次数.
     * @param integer $id 商品id.
     */
    public function getClickTimes($id){
        $redis=get_redis();
        $key = 'goods_clicks';
        //zIncrBy
        $this->ajaxReturn($redis->zIncrBy($key,1,$id));
    }

    /**
     * 将redis中的点击次数保存到数据库中.
     * @return bool|string
     */
    public function syncGoodsClicks() {
        $redis = get_redis();
        $key = 'goods_clicks';
        //获取到所有商品的点击次数
        $goods_clicks = $redis->zRange($key,0,-1,true);
        //dump($goods_clicks);exit;

        //如果Redis里面为空直接结束
        if(empty($goods_clicks)){
            return true;
        }

        //将redis中点击数保存到数据表中
        $goods_click_model = M('GoodsClick');
        //删除所有的已经存在的数据
        $goods_ids = array_keys($goods_clicks); //取出所有的键
        $goods_click_model->where(['goods_id'=>['in',$goods_ids]])->delete();
        //将redis中的数据保存到数据表中
        $data = [];
        foreach($goods_clicks as $key=>$value){
            $data[] = [
                'goods_id'=>$key,
                'click_times'=>$value,
            ];
        }
        //这是一条关闭浏览器的JS
        echo '<script type="text/javascript">window.close();</script>';
        
        return $goods_click_model->addAll($data);
    }

}