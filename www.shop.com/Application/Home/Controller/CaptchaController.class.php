<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4
 * Time: 15:55
 */

namespace Home\Controller;


use Think\Controller;

class CaptchaController extends Controller{
    public function captcha(){
        //验证码配置
        $setting = [
            'length'=>4, // 长度
        ];
        $verify = new \Think\Verify($setting); //创建模型
        $verify->entry();  // 建立验证码
    }

}