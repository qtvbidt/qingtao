<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/27
 * Time: 17:36
 */

namespace Admin\Controller;


use Think\Controller;
use Think\Upload;

/**
 * Description of UploadController
 *
 * @author qingf
 */
class UploadController extends Controller{
    public function uploadImg(){
        /*$options=[
            'rootPath'     => ROOT_PATH,
            'savePath'     => 'uploads/',
            //'maxSize'=>40,
        ];*/
        $options   = C('UPLOAD_SETTING');
        //创建upload对象
        $upload=new Upload($options);

        //执行上传,获取上传文件的信息
        $file_info = $upload->uploadOne($_FILES['file_data']);
       // $file_url = BASE_URL . '/' . $file_info['savepath'] . $file_info['savename'];

        if ($file_info) {
            if($upload->driver == 'Qiniu'){
                $file_url = $file_info['url'];
            } else{
                $file_url = BASE_URL . '/' . $file_info['savepath'] . $file_info['savename'];
            }

            $return = [

                'file_url' => $file_url,
                'msg'      => '上传成功',
                'status'   => 1,
            ];
        } else {
            $return = [
                'file_url' => '',
                'msg'      => $upload->getError(),
                'status'   => 0,
            ];
        }
        $this->ajaxReturn($return);

    }
    
}