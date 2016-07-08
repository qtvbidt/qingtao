<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/6
 * Time: 13:41
 */

namespace Home\Controller;


use Think\Controller;

class ApiController extends Controller{
    public function regSms($tel){
        Vendor('Alidayu.TopSdk');
        $c = new \TopClient;
        $c->appkey =  '23399156';
        $c->secretKey = 'a47e3adb03269becba5836e7019c1fc0';
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("云w123");
        //随机生成验证码
        $code = \Org\Util\String::randNumber(100000, 999999);

        //保存到session中
        session('reg_tel_code',$code);
        $data = [
            'product'=>'好人一生平安',
            'code'=> $code,
        ];
        $req->setSmsParam(json_encode($data));
        $req->setRecNum($tel);
        $req->setSmsTemplateCode("SMS_11520242");
        $resp = $c->execute($req);
        //dump($resp);
    }
}