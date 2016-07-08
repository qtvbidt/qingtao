<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 11:21
 */

namespace Admin\Model;


use Think\Model;

class ArticleCategoryModel extends Model{
    public function getPageResult($cond){
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

    public function getList() {
        return $this->where(['status'=>['gt',0]])->getField('id,name');
    }
}