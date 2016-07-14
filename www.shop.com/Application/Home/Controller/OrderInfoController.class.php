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
        //判断是否需要展示商品分类,首页展示,其它页面折叠
        $this->assign('show_category', false);

        //由于分类数据和帮助文章列表数据,不会频繁发生变化,但是请求又较为频繁,所以我们进行缓存
        if (!$goods_categories = S('goods_categories')) {
            //准备商品分类数据
            $goods_category_model = D('GoodsCategory');
            $goods_categories = $goods_category_model->getList('id,name,parent_id');
            S('goods_categories', $goods_categories,3600);
        }
        $this->assign('goods_categories', $goods_categories);


        if (!$help_article_list = S('help_article_list')) {
            //准备商品分类数据
            $article_category_model = D('Article');
            $help_article_list = $article_category_model->getHelpList();
            S('help_article_list', $help_article_list,3600);
        }
        //帮助文章分类
        $this->assign('help_article_list',$help_article_list);

        //获取用户登陆信息
        $this->assign('userinfo',login());

        $rows = $this->_model->getList();
        //传状态
        $this->assign('statuses',  $this->_model->statuses);
        $this->assign('rows',$rows);
        $this->display();
    }


    public function finish($id) {
        $order_info_model = M('OrderInfo');
        //把订单状态改为4
        if($order_info_model->where(['id'=>$id])->setField('status',4)===false){
            $this->error(get_error($order_info_model));
        }else{
            //跳回订单列表
            $this->success('交易完成,再来哦！',U('index'));
        }
    }
}