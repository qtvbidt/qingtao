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


    //支付接口
    public function alipay(){
        //为了中文不乱码
        header('Content-Type: text/html;charset=UTF-8');

        //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        //合作身份者id，以2088开头的16位纯数字
        $alipay_config['partner']		= '2088002155956432';

        //收款支付宝账号，一般情况下收款账号就是签约账号
        $alipay_config['seller_email']	= 'guoguanzhao520@163.com';

        //安全检验码，以数字和字母组成的32位字符
        $alipay_config['key']			= 'a0csaesgzhpmiiguif2j6elkyhlvf4t9';

        //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


        //签名方式 不需修改
        $alipay_config['sign_type']    = strtoupper('MD5');

        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset']= strtolower('utf-8');

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert']    = getcwd().'\\cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport']    = 'http';



        //在tp.表示/,如果是普通字符.要改成#
        vendor('Alipay.lib.alipay_submit#class');

        /**************************请求参数**************************/

        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        //表示支付宝操作完成,会发起一个请求通知你做后续操作,需要是公网地址
        $notify_url = "http://商户网关地址/create_partner_trade_by_buyer-PHP-UTF-8/notify_url.php";
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径,用户点击了付款,这时候就会跳转到一个页面.
        $return_url = "http://www.shop.com/Index/index";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no = '123213123';
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = '凉面';
        //必填

        //付款金额
        $price = '0.01';
        //必填

        //商品数量
        $quantity = "1";
        //必填，建议默认为1，不改变值，把一次交易看成是一次下订单而非购买一件商品
        //物流费用
        $logistics_fee = "0.00";
        //必填，即运费
        //物流类型
        $logistics_type = "EXPRESS";
        //必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
        //物流支付方式
        $logistics_payment = "SELLER_PAY";
        //必填，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
        //订单描述

        $body = '好吃';
        //商品展示地址
        $show_url = 'http://www.shop.com/Index/goods/id/5.html';
        //需以http://开头的完整路径，如：http://www.商户网站.com/myorder.html

        //收货人姓名
        $receive_name = '王二小';
        //如：张三

        //收货人地址
        $receive_address = '四川成都光华大道19号';
        //如：XX省XXX市XXX区XXX路XXX小区XXX栋XXX单元XXX号

        //收货人邮编
        $receive_zip = '000000';
        //如：123456

        //收货人电话号码
        $receive_phone = '';
        //如：0571-88158090

        //收货人手机号码
        $receive_mobile = '15987653456';
        //如：13312341234


        /************************************************************/

//构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_partner_trade_by_buyer",
            "partner" => trim($alipay_config['partner']),
            "seller_email" => trim($alipay_config['seller_email']),
            "payment_type"	=> $payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "price"	=> $price,
            "quantity"	=> $quantity,
            "logistics_fee"	=> $logistics_fee,
            "logistics_type"	=> $logistics_type,
            "logistics_payment"	=> $logistics_payment,
            "body"	=> $body,
            "show_url"	=> $show_url,
            "receive_name"	=> $receive_name,
            "receive_address"	=> $receive_address,
            "receive_zip"	=> $receive_zip,
            "receive_phone"	=> $receive_phone,
            "receive_mobile"	=> $receive_mobile,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        );

//建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;

    }
}