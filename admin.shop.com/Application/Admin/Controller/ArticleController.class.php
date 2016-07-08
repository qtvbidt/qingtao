<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 11:27
 */

namespace Admin\Controller;


use Think\Controller;

class ArticleController extends Controller{
    /**
     * @var \Admin\Model\ArticleModel
     * 
     */
    private $_model;//用一个属性来保存对象
    protected function _initialize(){
        $this->_model=D('Article');
    }

    //文章列表
    public function index(){
        //获取文章列表
        $name = I('get.name');
        $cond = [];
        if ($name) {
            $cond['name'] = ['like', '%' . $name . '%'];
        }
        $this->assign($this->_model->getPageResult($cond));

        //获取所有的文章分类
        $article_category_model = D('ArticleCategory');
        $categories             = $article_category_model->getList();
        $this->assign('categories', $categories);
        $this->display();
    }
    //添加
    public function add(){
        if(IS_POST){
            //收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->addArticle(I('post.'))===false){
                $this->error(get_error($this->_model));
            }
            $this->success('添加成功',U('index'));
        }else{
            //获取所有分类
            $ArticleCategory=M('ArticleCategory');
            $rows=$ArticleCategory->select();
            $this->assign('rows',$rows);
            $this->display();
        }
    }
    public function edit($id){
        if(IS_POST){
            //收集数据
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            //修改
            if($this->_model->editArt(I('post.'))===false){
                $this->error(get_error($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
            $rest=$this->_model->editArticle($id);
            $ArticleCategory=M('ArticleCategory');
            $rows=$ArticleCategory->select();
            //dump($rest['row']);exit;
            //$row=$rest['row'];
            $this->assign('rows',$rows);
            $this->assign('row',$rest['row']);
            $this->assign('ree',$rest['ree']);
            $this->display('add');
        }
    }

    public function remove($id){
        if($this->_model->removeArticle($id)===false){
            $this->error(get_error($this->_model));
        }else{
            $this->success('删除成功',U('index'));
        }
        
    }
    
}