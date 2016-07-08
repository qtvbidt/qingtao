<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4
 * Time: 17:43
 */

namespace Common\Behaviors;


use Think\Behavior;

class CheckPermissionBehavior extends Behavior{
    /**
     * @param mixed $params
     */
    public function run(&$params){
        //获取并验证权限
        //当前请求的地址
        $url = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
        //获取忽略列表
        $ignore_setting = C('ACCESS_IGNORE');

        //配置所有用户都可以访问的页面
        $ignore = $ignore_setting['IGNORE'];
        if(in_array($url, $ignore)){
            return true;
        }
        //获取用户信息
        $userinfo=login();
        //如果没有登陆就自动登陆
        if(!$userinfo){
            $userinfo = D('Admin')->autoLogin();
        }
        //如果是admin用户则获得所有权限，直接返回
        if(isset($userinfo['username']) && $userinfo['username'] == 'admin'){
            return true;
        }
        //获取权限列表
        $pathes = permission_pathes();
        //登陆用户可都可见的页面
        $user_ignore = $ignore_setting['USER_IGNORE'];
        //允许访问的页面有,角色处获取的权限和忽略列表
        $urls = $pathes;
        if($userinfo){
            //登陆用户可见页面还要额外加上登陆后的忽略列表
            $urls = array_merge($urls,$user_ignore);
        }
        //如果当前请求地址不在所充许访问的列表跳回登陆，否则什么都不作
        if(!in_array($url,$urls)){
            header('Content-Type: text/html;charset=utf-8');
            echo '<script type="text/javascript">top.location.href="'.U('Admin/Admin/login').'"</script>';
            redirect(U('Admin/Admin/login'), 3, '无权访问');
        }
    }

}