<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/24
 * Time: 17:45
 */

namespace Admin\Model;


use Think\Model;

class SupplierModel extends Model{
    //开启批量验证
    protected $patchValidate=true;
    //验证规则
    protected $_validate = [
        ['name','require','供货商名称不能为空'],
        ['name','','供货商已存在',self::EXISTS_VALIDATE,'unique'],
        ['status','0,1','供货商状态不合法',self::EXISTS_VALIDATE,'in'],
        ['sort','number','排序必须为数字'],
    ];
    
    public function getPageResult(array $cond=[]){
        //获取分页代码
        //获取分页配置
        $page_setting = C('PAGE_SETTING');
        //获取总行数
        $count = $this->where($cond)->count();
//        $page = new \Think\Page($count,2);
        $page = new \Think\Page($count,$page_setting['PAGE_SIZE']);
        //更改page样式
        $page->setConfig('theme', $page_setting['PAGE_THEME']);
        $page_html = $page->show();
//        dump($page_html);
        //获取分页数据
//        $rows = $this->where($cond)->page(I('get.p',1),2)->select();
        $rows = $this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
     //   dump($rows);exit;
        //返回
        return compact(['rows','page_html']);
        //return array('rows'=>$rows,'page_html'=>$page_html);
    }

}