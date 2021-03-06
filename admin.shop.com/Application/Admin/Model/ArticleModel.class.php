<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 11:49
 */

namespace Admin\Model;


use Think\Model;

class ArticleModel extends Model{
    //开启批量验证
    protected $patchValidate=true;
    //验证
    protected $_validate = [
        ['name','require','文章名称不能为空'],
        ['name','','文章已存在',self::EXISTS_VALIDATE,'unique'],
        ['status','0,1','文章状态不合法',self::EXISTS_VALIDATE,'in'],
        ['sort','number','排序必须为数字'],
    ];

    /**
     * 获取分页数据和分页代码。
     * @param array $cond 查询条件。
     */
    public function getPageResult(array $cond = []) {
        $cond         = array_merge(['status' => ['egt', 0]],$cond);
        //获取分页代码
        //获取分页配置
        $page_setting = C('PAGE_SETTING');
        //获取总行数
        $count        = $this->where($cond)->count();
        $page         = new \Think\Page($count, $page_setting['PAGE_SIZE']);
        //更改page样式
        $page->setConfig('theme', $page_setting['PAGE_THEME']);
        $page_html    = $page->show();
        //获取分页数据
        $rows         = $this->where($cond)->page(I('get.p', 1), $page_setting['PAGE_SIZE'])->select();
        return compact(['rows', 'page_html']);

    }
    
    
    /**
     * 使用关联查询获取分页数据和分页代码。
     * @param array $cond 查询条件。
     */
    /*public function getPageResult2(array $cond = []) {
        $cond         = array_merge(['a.status' => ['egt', 0]],$cond);
        //获取分页代码
        //获取分页配置
        $page_setting = C('PAGE_SETTING');
        //获取总行数
        $count        = $this->alias('a')->where($cond)->count();
        $page         = new \Think\Page($count, $page_setting['PAGE_SIZE']);
        //更改page样式
        $page->setConfig('theme', $page_setting['PAGE_THEME']);
        $page_html    = $page->show();
        //获取分页数据
        $rows         = $this->alias('a')->field('a.*,ac.name as cname')->where($cond)->join('__ARTICLE_CATEGORY__ as ac ON ac.id=a.article_category_id')->page(I('get.p', 1), $page_setting['PAGE_SIZE'])->select();
        return compact(['rows', 'page_html']);

    }*/
    //列表
    /*public function indexArticle(){
        $sql="select a.*,ac.name as category_name FROM article_category as ac JOIN article as a on a.article_category_id=ac.id";
        $rows  = $this->query($sql);
        //dump($rows);exit;
        return $rows;
    }*/

    //添加
    public function addArticle($data){
        //$arr=$this->data();
        //$arr['inputtime']=NOW_TIME;
        //$arr['inputtime']=time();
        $content=['content'=>$data['content']];
        $this->data['inputtime']=time();
        $article_id=$this->add();
        //echo $id; exit;
        $content['article_id']=$article_id;
       // dump($content);exit;
        //创建article_content表对象
        $article_contentModel=M('ArticleContent');
        $article_contentModel->add($content);

    }

    //修改时提到修改前的数据
    public function editArticle($id){
        $row=$this->find($id);
        $article_contentModel=M('ArticleContent');
        $ree=$article_contentModel->find($id);
        return compact('row','ree');
    }
    public function editArt($data){
        //dump($data);exit;
        //修改Article表
        $this->save();
        //修改article_content表中文章内容
        $article_contentModel=M('ArticleContent');
        $arr['article_id']=$data['id'];
        $arr['content']=$data['content'];
        $article_contentModel->save($arr);

    }
    
    public function removeArticle($id){
        if($this->delete($id)===false){
            return false;
        }else{
            $article_contentModel=M('ArticleContent');
            if($article_contentModel->delete($id)===false){
                return false;
            }
        }
        return true;
    }
}