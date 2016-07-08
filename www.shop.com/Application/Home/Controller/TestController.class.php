<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/5
 * Time: 18:51
 */

namespace Home\Controller;


use Think\Controller;

class TestController extends Controller{
    public function sms(){
        //phpinfo();exit;
        Vendor('Alidayu.TopSdk');
        $c = new \TopClient;
        $c->appkey =  '23399156';
        $c->secretKey = 'a47e3adb03269becba5836e7019c1fc0';
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("云w123");
        $req->setSmsParam("{'product':'好人','code':'4563'}");
        $req->setRecNum("13548135667");
        $req->setSmsTemplateCode("SMS_11520242");
        $resp = $c->execute($req);
    }







    public function sendEmail() {
        Vendor('PHPMailer.PHPMailerAutoload');

        $mail = new \PHPMailer;

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host       = 'smtp.qq.com';  //填写发送邮件的服务器地址
        $mail->SMTPAuth   = true;                               // 使用smtp验证
        $mail->Username   = '512874721@qq.com';                 // 发件人账号名
        $mail->Password   = 'pscyufruemiubhed';                           // 密码
        $mail->SMTPSecure = 'ssl';                            // 使用协议,具体是什么根据你的邮件服务商来确定
        $mail->Port       = 465;                                    // 使用的端口

        $mail->setFrom('512874721@qq.com','无极限俱乐部'); //发件人,注意:邮箱地址必须和上面的一致
        $mail->addAddress('369247763@qq.com');     // 收件人

        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = '欢迎注册无极限会员';
        $url = U('Member/Active', ['email' => '369247763@qq.com'], true, true);
        $mail->Body    = '欢迎您注册我们的网站,请点击<a href="' . $url . '">链接</a>激活账号.如果无法点击,请复制以下链接粘贴到浏览器窗口打开!<br />' . $url;
        $mail->CharSet = 'UTF-8';
        /*$mail->Body    = 'ewrwerw';
        $mail->CharSet = 'werwerew';*/
        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }
}