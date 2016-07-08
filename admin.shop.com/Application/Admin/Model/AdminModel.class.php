<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/2
 * Time: 11:38
 */

namespace Admin\Model;


use Org\Util\String;
use Think\Model;

class AdminModel extends Model{
    protected $patchValidate = true; // 批量验证
    /**
     * 1.username必填 唯一
     * 2.password必填 长度6-16位
     * 3.repassword 和password一致
     * 4.email 必填 唯一
     * @var type
     */
    protected $_validate = [
        ['username','require','用户名不能为空'],
        ['username','','用户名已被占用',self::EXISTS_VALIDATE,'unique','register'],
        //['password','require','密码不能为空',self::EXISTS_VALIDATE],
        ['password','6,16','密码长度不合法',self::VALUE_VALIDATE,'length'],
        ['repassword','password','两次密码不一致',self::EXISTS_VALIDATE,'confirm'],
        ['email','require','邮箱不能为空'],
        ['email','email','邮箱格式不合法',self::EXISTS_VALIDATE],
        ['email','','邮箱已被占用',self::EXISTS_VALIDATE,'unique'],
        ['captcha','checkCaptcha','验证码不正确',self::EXISTS_VALIDATE,'callback'],
    ];

    /**
     * 1. add_time 当前时间
     * 2. 盐 自动生成随机盐
     * @var type
     */
    protected $_auto = [
        ['add_time',NOW_TIME],
        ['salt','\Org\Util\String::randString',self::MODEL_BOTH,'function'],
        ['newpwd','\Org\Util\String::randString',self::MODEL_BOTH,'function']
    ];
    
    //验证码的验证
    public function checkCaptcha($code){
        $verify = new \Think\Verify();
        return $verify->check($code);
    }
    
    //添加管理员密码加盐加密
    public function addAdmin(){
        //dump($this->data);
        $this->startTrans(); // 开事务
        //对密码加盐加密
        $this->data['password'] = salt_mcrypt($this->data['password'], $this->data['salt']);
        //保存基本信息
        if(($admin_id = $this->add())===false){
            $this->rollback();
            return false;
        }
        //保存中间表中与角色的关联
        $admin_role_model = M('AdminRole'); //创模型
        $data=[];
        $role_ids=I('post.role_id');
        foreach($role_ids as $role_id){
            $data[] = [
                'admin_id'=>$admin_id,
                'role_id'=>$role_id,
            ];
        }
        //data里面有数据
        if($data){
            // 在中间表中插入多条数据
            if($admin_role_model->addAll($data)===false){
                $this->error = '保存角色关联不成功';
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        //exit;
        return true;

    }

    //分页展示
    public function getPageResult(array $cond=[]){
        //查询条件
        $cond = array_merge(['status'=>1],$cond);
        //查询总条数
        $count=$this->where($cond)->count();
        //获取配置
        $page_setting=C('PAGE_SETTING');
        //工具类对象
        $page = new \Think\Page($count, $page_setting['PAGE_SIZE']);
        //设置主题
        $page->setConfig('theme', $page_setting['PAGE_THEME']);
        //获取分页代码
        $page_html=$page->show();
        //获取分页数据
        $rows=$this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        return compact('page_html','rows');

    }

    public function getAdminInfo($id){
        $row = $this->find($id);
        $admin_role_model = M('AdminRole');
        $row['role_ids'] = json_encode($admin_role_model->where(['admin_id'=>$id])->getField('role_id',true));
        //dump($row);exit;
        return $row;
    }

    //修改管理员
    public function saveAdmin($id){
        $this->startTrans(); // 开事务
        //保存管理员和角色的关联
        $admin_role_model = M('AdminRole');
        //删除原有的关联
        if($admin_role_model->where(['admin_id'=>$id])->delete()===false){
        $this->error = '删除原有的角色失败';
        $this->rollback();
        return false;
        }
        //构建数组
        $data = [];
        $role_ids = I('post.role_id');
        foreach($role_ids as $role_id){
            $data[] = [
                'admin_id'=>$id,
                'role_id'=>$role_id,
            ];
        }
        if($data){
            //插入新的关联
            if($admin_role_model->addAll($data)===false){
                $this->error = '保存角色关联失败';
                $this->rollback();
                return false;
            }
        }
        
        $this->commit(); // 提交事务
        return true;
    }
    
    public function deleteAdmin($id){
        $this->startTrans();
        //删除admin中的管理员记录
        if($this->delete($id)===false){
            $this->rollback();
            return false;
        }
        //删除admin和role的关联关系
        $admin_role_model = M('AdminRole');
        //删除关联的角色
        if($admin_role_model->where(['admin_id'=>$id])->delete()===false){
            $this->error = '删除角色关联失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    //重置密码
    public function repassword($id){
        $password=I('post.password');
        $repassword=I('post.repassword');
        //没有输入密码
        if($password==='' && $repassword===''){
            $newpwd=$this->data['newpwd'];
        }elseif ($password===$repassword && $password!=''){ //密码相同且不为空
            $newpwd=$this->data['password'];
        }else{ //密码不相同
            $this->error = '两次输入密码不一致';
        }
        $this->data['password'] = salt_mcrypt($newpwd, $this->data['salt']);
        //dump($this->data);
        if($this->save()===false){
            $this->error = '修改密码失败';
        }
        //echo $newpwd;exit;
        return $newpwd;
            
    }
    
    public function login(){
        $username=$this->data['username'];
        $password=$this->data['password'];
        //获取用户信息,以便得到盐
        $userinfo = $this->getByUsername($username);
        if(!$userinfo){
            $this->error='用户名或密码不匹配';
            return false;
        }
        //验证密码
        $salt_password = salt_mcrypt($password, $userinfo['salt']);
        if($userinfo['password']!=$salt_password){
            $this->error='用户名或密码不匹配';
            return false;
        }
        //到这里验证通过可以登陆
        //保存用户的最后登陆时间和ip
        $data=[
            'last_login_time' => NOW_TIME,
            'last_login_ip' => get_client_ip(1),
            'id' => $userinfo['id'],
        ];
        $this->save($data);
        //将用户数据进行保存到session中
        login($userinfo);
        //获取用户权限并保存到session
        $this->getPermissions($userinfo['id']);
        // 登陆后删除数据表中token
        $admin_token_model = M('AdminToken');
        $admin_token_model->delete($userinfo['id']);
        //如果勾选保存密码，保存cookie
        if(I('post.remember')){
            //生成cookie和数据表数据,生成一个随机字符串token
            $data=[
                'admin_id' => $userinfo['id'],
                'token' => \Org\Util\String::randString(40),
            ];
            cookie('USER_AUTO_LOGIN_TOKEN',$data,604800); // 保存到cookie一周
            
            $admin_token_model->add($data); // 保存到数据表
        }
        return $userinfo;
    }
    //获取并保存用户权限,id  path
    public function getPermissions($admin_id){
        //SELECT DISTINCT path FROM admin_role AS ar JOIN role_permission AS rp ON ar.`role_id`=rp.`role_id` JOIN permission AS p ON p.`id`=rp.`permission_id` WHERE path<>'' AND admin_id=1
        $cond = [
            'path' => ['neq', ''],
            'admin_id' => $admin_id,
        ];
        $permissions = M()->distinct(true)->field('permission_id,path')->table('admin_role')->alias('ar')->join('__ROLE_PERMISSION__ as rp ON ar.`role_id`=rp.`role_id`')->join('__PERMISSION__ as p ON p.`id`=rp.`permission_id`')->where($cond)->select();
        $pids = [];
        $paths = [];
        foreach ($permissions as $permission) {
            $paths[] = $permission['path'];
            $pids[] = $permission['permission_id'];
        }
        permission_pathes($paths);
        permission_pids($pids);
        return true;
    }
    
    //自动登陆
    public function autoLogin(){
        // 取出cookie中tookie相关数据
        $data = cookie('USER_AUTO_LOGIN_TOKEN');
        // 如果$data里面没有数据刚直接返回
        if (!$data) {
            return false;
        }
        // 如果有则和数据表中的数据作对比
        $admin_token_model = M('AdminToken');
        // 如果对比不上直接返回
        if(!$admin_token_model->where($data)->count()){
            return false;
        }
        //对比上之后，为了避免token被窃取,自动登陆一次就重置
        //删除数据表中原来相关数据
        $admin_token_model->delete($data['admin_id']);
        $data=[
            'admin_id' => $data['admin_id'],
            'token' => \Org\Util\String::randString(40),
        ];
        cookie('USER_AUTO_LOGIN_TOKEN',$data,604800); // 保存到cookie一周

        $admin_token_model->add($data); // 把新的token保存到数据表
        //获取用户信息，保存用户信息到session中
        $userinfo=$this->find($data['admin_id']);
        login($userinfo);
        //获取并保存用户权限
        $this->getPermissions($userinfo['id']);
        return $userinfo;
    }

}