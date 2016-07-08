<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 19:03
 */

namespace Admin\Controller;


use Think\Controller;

class RoleController extends Controller{
    /**
     * @var \Admin\Model\RoleModel
     */
    private $_model = null;

    protected function _initialize() {
        $this->_model = D('Role');
    }
    
    public function index(){
        //搜索条件
        $name = I('get.name');
        $cond = [];
        if ($name) {
            $cond['name'] = [
                'like', '%' . $name . '%'
            ];
        }
        $this->assign($this->_model->getPageResult($cond));
        $this->display();
    }
    
    public function add(){
        if(IS_POST){
            //收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            //保存数据
            if ($this->_model->addRole()===false) {
                $this->error(get_error($this->_model));
            }
            //跳转
            $this->success('添加成功', U('index'));
        }else{
            $this->_before_view();
            $this->display();
        }
    }
    
    
    public function edit($id){
        if(IS_POST){
            //收集数据
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            //保存数据
            if ($this->_model->saveRole() === false) {
                $this->error(get_error($this->_model));
            }
            //跳转到index页面
            $this->success('修改成功', U('index'));
        }else{
            //获取角色及其对应的权限
            $row = $this->_model->getPermissionInfo($id);
            $this->assign('row',$row);
            $this->_before_view();
            $this->display('add');
        }
    }
    
    //删除角色
    public function remove($id){
        if($this->_model->deleteRole($id) === false){
            $this->error(get_error($this->_model));
        }
        $this->success('删除成功', U('index'));
    }


    private function _before_view() {
        //获取所有权限
        $permission_model = D('Permission');
        $permissions = $permission_model->getList();
        //传递
        $this->assign('permissions', json_encode($permissions));
    }
}