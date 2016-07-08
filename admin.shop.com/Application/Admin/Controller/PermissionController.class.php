<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 17:37
 */

namespace Admin\Controller;


use Think\Controller;

class PermissionController extends Controller{

    /**
     * @var \Admin\Model\PermissionModel
     */
    private $_model = null;

    protected function _initialize() {
        $this->_model = D('Permission');
    }
    public function index(){
        //获取数据
        $rows=$this->_model->getList();
        //传递数据
        $this->assign('rows',$rows);
        $this->display();
    }

    public function add(){
        if(IS_POST){
            //收集传过来的数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            //保存数据到权限表
            if($this->_model->addPermission()===false){
                $this->error(get_error($this->_model));
            }
            $this->success('添加成功','index');
        }else{
            //准备父级权限,也就是查出所有的权限列表
            $this->_before_view();
            $this->display();
        }
    }

    public function edit($id){
        if(IS_POST){
            //收集传过来的数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            //保存数据到权限表
            if($this->_model->savePermission()===false){
                $this->error(get_error($this->_model));
            }
            //跳转
            $this->success('修改成功','index');
        }else{
            //获取数据
            $row=$this->_model->find($id);
            //传递数据
            $this->assign('row',$row);
            $this->_before_view();
            $this->display('add');
        }
        
    }
    
    //删除当前节点及后代节点
    public function remove($id){
        if($this->_model->deletePermission($id) === false){
            $this->error(get_error($this->_model));
        }
        //跳转
        $this->success('删除成功', U('index'));
    }

    private function _before_view() {
        $permissions = $this->_model->getList();
        array_unshift($permissions, ['id' => 0, 'name' => '顶级权限', 'parent_id' => null]);
        $this->assign('permissions', json_encode($permissions));
    }
}