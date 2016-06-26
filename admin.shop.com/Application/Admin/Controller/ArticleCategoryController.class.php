<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 10:32
 */

namespace Admin\Controller;


use Think\Controller;

class ArticleCategoryController extends Controller{
    private $_model;
    protected function _initialize(){
        $this->_model=D('ArticleCategory');
    }
    //文章展示
    public function index(){
        $name=I('get.name');
        $cond['starus']=['gt',0];
        if($name){
            $cond['name']=['like','%'.$name.'%'];
        }

        //获取数据
        $data=$this->_model->getPageResult($cond);
        $this->assign($data);
        //展示
        $this->display();
    }

    //添加文章分类
    public function add(){
        //是否有POST数据提交
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

    public function edit($id){
        if(IS_POST){
            //用create收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->save()===false){
                $this->error(get_error($this->_model));
            }else{
                $this->success('修改成功',U('index'));
            }
        }else{
            //获取当前分类数据
            $row=$this->_model->find($id);
            $this->assign('row',$row);
            $this->display('add');
        }
    }

    //逻辑删除
    public function remove($id){
        $data = [
            'id'=>$id,
            'status'=>-1,
            'name'=>['exp','concat(name,"_del")'],
        ];
        if($this->_model->setField($data)===false){
            $this->error(get_error($this->_model));
        }else{
            $this->success('删除成功',U('index'));
        }
    }

}