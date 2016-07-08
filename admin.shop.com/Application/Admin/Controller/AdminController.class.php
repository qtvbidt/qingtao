<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/2
 * Time: 11:10
 */

namespace Admin\Controller;


use Think\Controller;

class AdminController extends Controller{
    /**
     * @var \Admin\Model\AdminModel
     */
    private $_model = null;

    protected function _initialize() {
        $this->_model = D('Admin');
    }

    public function index(){
        //获取管理员列表
        $name = I('get.name');
        $cond = [];
        if ($name) {
            $cond['username'] = ['like', '%' . $name . '%'];
        }
        $this->assign($this->_model->getPageResult($cond));
        $this->display();
    }
    
    public function add(){
        if(IS_POST){
            if ($this->_model->create('','register') === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->addAdmin() === false) {
                $this->error(get_error($this->_model));
            }
            $this->success('添加成功', U('index'));
        }else{
            $this->_before_view();
            $this->display();
        }
    }
    public function edit($id){
        if(IS_POST){
            //收集数据，虽然没用上但可以过滤一些东西，还是要写
            if ($this->_model->create('','register') === false) {
                $this->error(get_error($this->_model));
            }
            //保存数据
            if ($this->_model->saveAdmin($id) === false) {
                $this->error(get_error($this->_model));
            }
            // 跳转
            $this->success('修改成功', U('index'));
        }else{
            //获取管理员信息,包括角色
            $row = $this->_model->getAdminInfo($id);
            $this->assign('row',$row);
            //获取所有角色列表
            $this->_before_view();
            $this->display('add');
        }
        
    }
    public function remove($id){
        // 删除
        if($this->_model->deleteAdmin($id)===false){
            $this->error(get_error($this->_model));
        }
        //跳转
        $this->success('删除成功', U('index'));

    }

    //重置密码
    public function changePassword($id){
        if(IS_POST){
            //收集数据，虽然没用上但可以过滤一些东西，还是要写
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            $rep=$this->_model->repassword($id);
            echo '你的新密码为：'.$rep;exit;
        }else{
            $row=$this->_model->find($id);
            $this->assign('row',$row);
            $this->display();
        }
    }

    private function _before_view() {
        //获取所有的角色列表
        $role_model = D('Role');
        //传递数据
        $roles = $role_model->getList();
        $this->assign('roles', json_encode($roles));
    }

    public function login(){
        if(IS_POST){
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->login() === false) {
                $this->error(get_error($this->_model));
            }
            $this->success('登陆成功', U('Index/index'));
        }else{
            $this->display();
        }
    }

    //退出功能
    public function logout() {
        session(null); // 清空session
        cookie(null); // 清空cookie
        $this->success('退出成功',U('login'));
    }
}