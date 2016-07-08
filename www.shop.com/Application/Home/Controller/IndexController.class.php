<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    protected function _initialize(){
        //判断是否需要展示商品分类,首页展示,其它页面折叠
        if (ACTION_NAME == 'index') {
            $show_categroy = true;
        } else {
            $show_categroy = false;
        }
        $this->assign('show_category', $show_categroy);

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
    }
    public function index(){
        $goods_model = D('Goods');
        $data = [
            'goods_best_list'=>$goods_model->getListByGoodsStatus(1),
            'goods_new_list'=>$goods_model->getListByGoodsStatus(2),
            'goods_hot_list'=>$goods_model->getListByGoodsStatus(4),
        ];
        $this->assign($data);
        $this->display();
     }

    public function goods($id){
        //获取商品详情
        $goods_model = D('Goods');

        if(!$row = $goods_model->getGoodsInfo($id)){
            $this->error('您查看的商品没有了,下次再来哦',U('index'));
        }
        $this->assign('row',$row);
        $this->display();
    }
}