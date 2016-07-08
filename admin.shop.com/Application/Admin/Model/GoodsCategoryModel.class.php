<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/27
 * Time: 22:51
 */

namespace Admin\Model;


use MediaCore\Lib\NestedSets\NestedSets;
use Think\Model;

class GoodsCategoryModel extends Model{
    protected $patchValidate = true; //开启批量验证
    /**
     * name 必填
     */
    protected $_validate     = [
        ['name', 'require', '商品分类名称不能为空'],
    ];

    /**
     * 获取所有的商品分类。
     * @return array
     */
    public function getList() {
        return $this->where(['status'=>['egt',0]])->order('lft')->select();
    }

    /**
     * 完成分类的添加，和计算左右节点和层级的功能。
     * 使用nestedsets实现
     */
    public function addCategory(){
        //删除主键，因为不需要
        unset($this->data[$this->getPk()]);
        $orm = D('MySQL','Logic');
        $nestedsets = new \Admin\Logic\NestedSets($orm, $this->trueTableName, 'lft', 'rght', 'parent_id', 'id', 'level');
        //计算左右结点，保存数据 
        return $nestedsets->insert($this->data['parent_id'], $this->data, 'bottom');

    }

    /**
     * 删除的方法
     * @param $id
     */
    public function deleteCategory($id){
        //获取当前的父级分类
        //创建ORM对象
        $orm = D('MySQL', 'Logic');
        //创建nestedsets对象
        $nestedsets = new \Admin\Logic\NestedSets($orm, $this->trueTableName, 'lft', 'rght', 'parent_id', 'id', 'level');
        //delete会将所有的后代分类一并删除,并且重新计算相关节点的左右节点.
        return $nestedsets->delete($id);
    }

    /**
     * 修改的方法
     * @param $id
     */
    public function saveCategory(){
        //判断是否修改了父级分类,如果没修改,就不要创建nestedsets
        //获取原来的父级分类,要使用getFieldById因为find会将数据放到data属性中
        $parent_id=$this->getFieldById($this->data['id'],'parent_id');
        if($parent_id!=$this->data['parent_id']){
            //获取当前的父级分类
            //创建ORM对象
            $orm = D('MySQL','Logic');
            //创建nestedsets对象
            $nestedsets = new \Admin\Logic\NestedSets($orm, $this->trueTableName, 'lft', 'rght', 'parent_id', 'id', 'level');
            //moveUnder只计算左右节点和层级，不保存其它数据
            if ($nestedsets->moveUnder($this->data['id'], $this->data['parent_id'], 'bottom') === false) {
                $this->error = '不能将分类移动到后代分类下';
                return false;
            }
        }
        $this->save();
    }

}