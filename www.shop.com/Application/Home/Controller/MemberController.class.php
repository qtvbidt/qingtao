<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/5
 * Time: 16:23
 */

namespace Home\Controller;


use Think\Controller;

class MemberController extends Controller{
    /**
     * @var \Home\Model\MemberModel
     */
    private $_model = null;
    protected function _initialize() {
        $this->_model=D('Member');

        $mete_titles = [
            'reg'=>'用户注册',
            'login'=>'用户登陆',
        ];
        $meta_title = (isset($mete_titles[ACTION_NAME])?$mete_titles[ACTION_NAME]:'无极限');
        $this->assign('meta_title',$meta_title);


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
    }
    
    //注册
    public function reg(){
        if(IS_POST){
            //收集数据
            if ($this->_model->create(',','reg') === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->addMember() === false) {
                $this->error(get_error($this->_model));
            }
            $this->success('注册成功', U('Index/index'));
        }else{
            $this->display();
        }
    }

    /**
     * 用户登陆，获取和保存用户信息，把购物车cookie数据保存到数据表中
     */
    public function login(){
        if(IS_POST){
            //收集数据
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->login() === false) {
                $this->error(get_error($this->_model));
            }
            $url = cookie('__FORWARD__');
            cookie('__FORWARD__',null);
            if(!$url){
                $url = U('Index/index');
            }
            $this->success('登录成功', $url);
        }else{
            $this->display();
        }
    }

    /**
     * 激活邮件.
     * @param type $email
     * @param type $register_token
     */
    public function active($email,$register_token){
        /*dump($email);
        dump($register_token);
        echo "<br/>";
        dump(I('get.'));*/
        //查询有没有一个记录,邮箱和token和传过来的一致的
        $cond = [
            'email'=>$email,
            'register_token'=>$register_token,
            'status'=>0,
        ];
        //如果有
        if($this->_model->where($cond)->count()){
            //修改状态
            $this->_model->where($cond)->setField('status',1);
            $this->success('激活成功',U('Index/index'));
        }else{
            //如果没有
            $this->error('验证失败',U('Index/index'));
        }
    }

    /**
     * 检查注册信息是否已经被占用.
     * 检查用户名,邮箱,手机号码.
     */
    public function checkByParam(){
        $cond=I('get.');
        if($this->_model->where($cond)->count()){
            $this->ajaxReturn(false);
        }else{
            $this->ajaxReturn(true);
        }
    }

    public function logout() {
        session(null);
        cookie(null);
        $this->success('退出成功',U('Index/index'));
    }

    public function userinfo(){
        //获用户信息
        $userinfo=login();
        if($userinfo){
            //有用户信息返回用户名
            $this->ajaxReturn($userinfo['username']);
        }else{
            $this->ajaxReturn(false); //没用户信息
        }
    }

    //收货地址列表页面
    public function locationIndex(){
        if(!login()){
            $this->success('请先登陆',U('Member/login'));
            exit;
        }
        //获取省份列表
        $location_model = D('Locations');
        $provices = $location_model->getListByParentId();
        $this->assign('provinces',$provices); // 传入所有省
        //取出当前用户所在收货地址
        $address_model = D('Address');
        //传收货地址到页面
        $this->assign('addresses',$address_model->getList());
        $this->display();
    }


    /**
     * 获取下级城市列表，使用json方式返回。
     * @param $parent_id
     */
    public function getLocationListByParentId($parent_id) {
        //获取省份列表
        $location_model = D('Locations');
        $provices = $location_model->getListByParentId($parent_id);
        $this->ajaxReturn($provices);
    }


    //添加收获地址
    public function addLocation() {
        //准备数据
        $address_model = D('Address');
        if($address_model->create()===false){
            $this->error(get_error($address_model));
        }
        if($address_model->addAddress()===false){
            $this->error(get_error($address_model));
        }
        $this->success('添加完成',U('locationIndex'));
    }



    //修改收获地址
    public function modifyLocation($id) {
        $address_model = D('Address');
        if(IS_POST){
            //准备数据
            //$address_model = D('Address');
            if($address_model->create()===false){
                $this->error(get_error($address_model));
            }
            if($address_model->saveddress($id)===false){
                $this->error(get_error($address_model));
            }
            $this->success('修改完成',U('locationIndex'));
        }else{
            //当前地址的详细信息
            //$address_model = D('Address');
            $row = $address_model->getAddressInfo($id);
            $this->assign('row',$row);
            //获取省份列表
            $location_model = D('Locations');
            $provices = $location_model->getListByParentId();
            $this->assign('provinces',$provices);
            $this->display();
        }
    }

}