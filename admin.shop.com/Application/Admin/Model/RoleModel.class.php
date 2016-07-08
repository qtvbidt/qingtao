<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 19:23
 */

namespace Admin\Model;


use Think\Model;

class RoleModel extends Model{

    public function addRole(){
        //开启事务
        $this->startTrans();
        //保存角色数据
        if(($role_id=$this->add())===false){
            $this->rollback(); // 回滚事务
            return false;
        }
        //保存角色对应的权限到中间表
        //获取角色对应的权限id
        $permission_ids = I('post.permission_id');
        $data = [];
        foreach($permission_ids as $permission_id){
            $data[] = [
                'role_id'=>$role_id,
                'permission_id'=>$permission_id,
            ];
        }
        if($data){
            $role_permission_model = M('RolePermission');
            //一次插入多条数据
            if($role_permission_model->addAll($data) ===false){
                $this->error = '保存权限失败';
                $this->rollback();
                return false;
            }
        }

        $this->commit(); //提交事务
        return true;
    }

    public function getPageResult(array $cond=[]){
        //查询条件
        $cond = array_merge(['status'=>1],$cond);
        //总行数
        $count = $this->where($cond)->count();
        //获取配置
        $page_setting = C('PAGE_SETTING');
        //工具类对象
        $page = new \Think\Page($count, $page_setting['PAGE_SIZE']);
        //设置主题
        $page->setConfig('theme', $page_setting['PAGE_THEME']);
        //获取分页代码
        $page_html = $page->show();
        //获取分页数据
        $rows = $this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        return compact('rows', 'page_html');
    }
    
    public function getPermissionInfo($id){
        //获取基本信息
        $row = $this->find($id);
        //获取关联权限
        $role_permission_model = M('RolePermission');
        $row['permission_ids'] = json_encode($role_permission_model->where(['role_id'=>$id])->getField('permission_id',true));
        return $row;
    }
    
    public function saveRole(){
        $this->startTrans(); // 开启事务
        $role_id = $this->data['id']; //保存ID
        //保存基本信息
        if ($this->save() === false){
            $this->rollback();
            return false;
        }
        //删除中间表中原有的当前角色权限关联
        $role_permission_model = M('RolePermission');
        if($role_permission_model->where(['role_id'=>$role_id])->delete()===false){
            $this->error = '删除历史权限失败';
            $this->rollback();
            return false;
        }
        //保存中间表中当前角色现在指定关联的权限
        $permission_ids = I('post.permission_id');
        $data = [];
        foreach($permission_ids as $permission_id){
            $data[] = [
                'role_id'=>$role_id,
                'permission_id'=>$permission_id,
            ];
        }
        if($data){
            if($role_permission_model->addAll($data) ===false){
                $this->error = '保存权限失败';
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }
    
    public function deleteRole($id){
        $this->startTrans(); // 开事务
        //删除角色记录基本信息
        if($this->delete($id) === false){
            $this->rollback();
            return false;
        }
        //删除中间表中当前角色权限关联
        $role_permission_model = M('RolePermission');
        if($role_permission_model->where(['role_id'=>$id])->delete()===false){
            $this->error = '删除权限关联失败';
            $this->rollback();
            return false;
        }

        //删除管理员关联
        $admin_role_model = M('AdminRole');
        if($admin_role_model->where(['role_id'=>$id])->delete()===false){
            $this->error = '删除管理员关联失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    public function getList() {
        return $this->where(['status'=>1])->select();
    }
}