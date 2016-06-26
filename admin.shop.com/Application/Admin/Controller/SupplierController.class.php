<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/24
 * Time: 16:50
 */

namespace Admin\Controller;


use Think\Controller;

class SupplierController extends Controller{
    private $_model;
    protected function _initialize(){
        $this->_model=D('Supplier');
    }
    //供应商列表
    public function index(){
        $name=I('get.name');
        $cond['status']=['egt',0];
        if($name){
            $cond['name']=['like','%'.$name.'%'];
        }
        //echo $name;exit;
        //创建模型
        //查数据
        $data=$this->_model->getPageResult($cond);
        //加载视图
        $this->assign($data);
        $this->display('index');
    }

    //供应商添加
    public function add(){
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            
            if($this->_model->add()===false){
                $this->error(get_error($this->_model));
            }else{
                $this->success('添加成功',U('index'));
            }
        }else{
            $this->display();
        }

    }

    //供应商修改
    public function edit($id){
        if(IS_POST){
            //收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->save()===false){
                $this->error(get_error($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
            //获取当前供货商信息
            $row=$this->_model->find($id);
            $this->assign('row',$row);
            $this->display('add');
        }
    }

    //供应商删除（逻辑删除）
    public function remove($id){
        $data = [
            'id'=>$id,
            'status'=>-1,
            'name'=>['exp','concat(name,"_del")'],
        ];
        //如果只是更新个别字段的值，可以使用setField方法。
        //setField方法支持同时更新多个字段，只需要传入数组即可
        if($this->_model->setField($data)===false){
            $this->error(get_error($this->_model));
        }else{
            $this->success('删除成功',U('index'));
        }
    }
}