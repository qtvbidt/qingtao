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

    public function login(){
        if(IS_POST){
            //收集数据
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->login() === false) {
                $this->error(get_error($this->_model));
            }
            $this->success('注册成功', U('Index/index'));
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

}