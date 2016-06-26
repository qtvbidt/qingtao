<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 9:23
 */

namespace Admin\Controller;


use Think\Controller;

class BrandController extends Controller{
    private $_model;//用一个属性来保存对象
    protected function _initialize(){
        $this->_model=D('Brand');
    }
    //展示品牌列表
    public function index(){
        $name=I('get.name');
        $cond['status']=['gt',0];
        if($name){
            $cond['name']=['like','%'.$name.'%'];
        }
        //获取数据
        $data=$this->_model->getPageResult($cond);
        $this->assign($data);
        //展示页面
        $this->display();
    }
    
    //添加品牌
    public function add(){
        if(IS_POST){
            //收集数据
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
    //修改的方法
    public function edit($id){
        if(IS_POST){
            //收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->save()===false){
                //echo gdgdgd;
                $this->error(get_error($this->_model));
            }else{
                $this->success('修改成功',U('index'));
            }

        }else{
            //获取当前数据
            $row=$this->_model->find($id);
            $this->assign('row',$row);
            $this->display('add');
        }
    }

    //逻辑删除
    public function remove($id){
        $data=[
            'id'=>$id,
            'status'=>-1,
            'name'=>['exp','concat(name,"_del")']
        ];
        if($this->_model->setField($data)===false){
            $this->error(get_error($this->_model));
        }else{
            $this->success('删除成功',U('index'));
        }
    }
}