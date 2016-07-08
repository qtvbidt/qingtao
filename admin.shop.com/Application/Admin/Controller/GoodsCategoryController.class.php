<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/27
 * Time: 22:48
 */

namespace Admin\Controller;


use Think\Controller;

class GoodsCategoryController extends Controller{
    /**
     * @var \Admin\Model\GoodsCategoryModel
     */
    private $_model = null; // 创建个属性用来保存对象

    protected function _initialize() {
        $this->_model = D('GoodsCategory');
    }

    //展示分娄列表的方法
    public function index(){
        $this->assign('rows',$this->_model->getList());
        $this->display();
    }

    //添加商品分类
    public function add() {
        if (IS_POST) {
            //收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->addCategory() === false){
                $this->error(get_error($this->_model));
            }
            $this->success('添加成功',U('index'));
        } else {
            $this->_before_view();
            $this->display();
        }
    }

    //修改商品分类
    public function edit($id) {
        if (IS_POST) {
            //收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->saveCategory() === false){
                $this->error(get_error($this->_model));
            }
            $this->success('修改成功',U('index'));
        } else {
            //展示数据
            $row = $this->_model->find($id);
            $this->assign('row', $row);
            //获取所有的分类
            $this->_before_view();
            $this->display('add');
        }
    }

    //删除商品分类
    public function remove($id) {
        if($this->_model->deleteCategory($id)===false){
            $this->error(get_error($this->_model));
        }else{
            $this->success('删除成功',U('index'));
        }
    }

    //获取所有商品分类，并加上一个顶级分类
    private function _before_view(){
        $goods_categories = $this->_model->getList();
        array_unshift($goods_categories, ['id' => 0, 'name' => '顶级分类', 'parent_id' => 0]);
        $goods_categories = json_encode($goods_categories);
        $this->assign('goods_categories', $goods_categories);
    }

}