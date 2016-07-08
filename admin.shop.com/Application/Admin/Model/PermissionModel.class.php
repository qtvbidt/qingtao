<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 17:43
 */

namespace Admin\Model;


use Think\Model;

class PermissionModel extends Model{
    protected $_validate = [
        ['name', 'require', '权限名称不能为空']
    ];

    /**
     * 获取分类列表.
     * @return type
     */
    public function getList() {
        return $this->where(['status' => 1])->order('lft')->select();
    }
    //使用nestedsets添加权限
    public function addPermission(){
        //删除主键
        unset($this->data[$this->getPk()]);
        //创建orm
        $orm = D('MySQL', 'Logic');
        //创建nestedsets对象
        $nestedsets = new \Admin\Logic\NestedSets($orm, $this->getTableName(), 'lft', 'rght', 'parent_id', 'id', 'level');
        //               插入方法       父级字段id             要插入的数组
        if ($nestedsets->insert($this->data['parent_id'], $this->data, 'bottom') === false) {
            $this->error = '添加失败';
            return false;
        }
        return true;
    }

    public function savePermission(){
        //判断是否有修改父类，没有的话就不用去建nestedsets对象
        $parent_id = $this->getFieldById($this->data['id'], 'parent_id');
        if($parent_id!=$this->data['parent_id']){
            //创建orm
            $orm = D('MySQL', 'Logic');
            //创建nestedsets对象
            $nestedsets = new \Admin\Logic\NestedSets($orm, $this->getTableName(), 'lft', 'rght', 'parent_id', 'id', 'level');
            //               修改方法       父级字段id             要插入的数组
            if ($nestedsets->moveUnder($this->data['id'], $this->data['parent_id'], 'bottom') === false) {
               //只重新计算了边界没有改其它
                $this->error = '添加失败';
                return false;
            }
        }
        //保存基本数据
        return $this->save();

    }

    public function deletePermission($id){
        $this->startTrans(); // 开启事务
        //获取后代权限
        //找到当前节点的左右边界
        $permission_info=$this->field('lft,rght')->find($id);
        $cond=[
            'lft'=>['egt',$permission_info['lft']],
            'rght'=>['elt',$permission_info['rght']],
        ];
        //通过父级边界，找到所有后代权限的id
        $permission_ids=$this->where($cond)->getField('id',true);
        //删除角色-权限中间表的相关权限记录
        $role_permission_model = M('RolePermission');
        if($role_permission_model->where(['permission_id'=>['in',$permission_ids]])->delete()===false){
            $this->error = '删除角色-权限关联失败';
            $this->rollback();
            return false;
        }
        
        //删除菜单和权限的关联
        $menu_permission_model = M('MenuPermission');
        //先删除历史关系
        //查询出子级菜单列表
        if ($menu_permission_model->where(['permission_id' =>$id])->delete() === false) {
            $this->error = '删除菜单-权限关联失败';
            $this->rollback();
            return false;
        }
        
        //要重新计算左右边界
        //创建orm
        $orm = D('MySQL', 'Logic');
        //创建nestedsets对象
        $nestedsets = new \Admin\Logic\NestedSets($orm, $this->getTableName(), 'lft', 'rght', 'parent_id', 'id', 'level');
        //删除表中数据及后代权限 ，并重新计算边界
        if ($nestedsets->delete($id) === false) {
            $this->error = '删除失败';
            $this->rollback();
            return false;
        } else {
            $this->commit();
            return true;
        }
    }
}