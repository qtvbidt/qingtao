<?php
/**
 * Created by PhpStorm.
 * User: qingf
 * Date: 2016/7/7
 * Time: 11:44
 */

namespace Home\Model;


use Think\Model;

class ArticleModel extends Model{

    /**
     * 获取帮助文章列表
     */
    public function getHelpList() {
        //获取所有的分类
        $article_category_model = M('ArticleCategory');
        $article_categories = $article_category_model->where(['status'=>1,'is_help'=>1])->getField('id,name');
        //获取每个分类的文章列表
        $return = [];
        foreach($article_categories as $key=>$value){
            $articles = $this->field('id,name')->order('sort')->limit(6)->where(['status'=>1,'article_category_id'=>$key])->select();
            $return[$value] = $articles;
        }
        return $return;
    }
}