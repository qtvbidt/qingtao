<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 10:06
 */

namespace Admin\Model;


use Think\Model;

class BrandModel extends Model{
    //开启批量验证
    protected $patchValidate=true;
    //验证
    protected $_validate = [
        ['name','require','品牌名称不能为空'],
        ['name','','品牌已存在',self::EXISTS_VALIDATE,'unique'],
        ['status','0,1','品牌状态不合法',self::EXISTS_VALIDATE,'in'],
        ['sort','number','排序必须为数字'],
    ];
    //获取分页且正常的品牌数据
    /**
     * @param array $cond
     * @return mixed
     */
    public function getPageResult(array $cond=[]){
        //获取分页配置
        $page_setting=C('PAGE_SETTING');
        //获取总行数
        $count=$this->where($cond)->count();
        //获取分页html代码
        $page=new \Think\Page($count,$page_setting['PAGE_SIZE']);
        //$page = new \Think\Page($count,$page_setting['PAGE_SIZE']);
        //更改page样式
        $page->setConfig('theme', $page_setting['PAGE_THEME']);
        $page_html = $page->show();
        //获取分页数据
        $rows = $this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        return compact('rows','page_html');
    }

    /**
     * 获取所有的商品分类。
     * @return array
     */
    public function getList() {
        return $this->where(['status'=>['gt',0]])->select();
    }
}