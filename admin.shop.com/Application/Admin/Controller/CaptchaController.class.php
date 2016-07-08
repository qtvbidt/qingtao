<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4
 * Time: 15:55
 */

namespace Admin\Controller;


use Think\Controller;

class CaptchaController extends Controller{
    public function captcha(){
        $setting = [
            'length'=>4,
        ];
        $verify = new \Think\Verify($setting);
        $verify->entry();
    }

}