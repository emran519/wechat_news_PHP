<?php
include '../include/oreo.cron.php';

class controller
{
    // 设置能访问的域名
    static public $originarr = [
        'http://127.0.0.1',
        '您的WEB端业务域名'
    ];

    static public function setHeader()
    {
        // 获取当前跨域域名
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, self::$originarr)) {
            // 允许 $originarr 数组内的 域名跨域访问
            header('Access-Control-Allow-Origin:' . $origin);
            // 响应类型
            header('content-type:application/json;charset=utf-8');
            // 带 cookie 的跨域访问
            header('Access-Control-Allow-Credentials: true');
            // 响应头设置
            header('Access-Control-Allow-Headers:x-requested-with,Content-Type,X-CSRF-Token');
        }
    }

    //函数选择器
    public function isRequestUrl($url)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $param = $_POST;
        }else{
            $param = $_GET;
        }
        //上传图片
        if($url == 'upload_photo'){ //上传图片
            return $this->uploadPhoto($param);
        }else if($url == 'user_list'){ //用户列表
            return $this->userList($param);
        }else if($url == 'article_list'){ //新闻列表
            return $this->articleList($param);
        }else if($url == 'article_add'){ //添加新闻
            return $this->articleAdd($param);
        }else if($url == 'article_info'){ //编辑新闻页面获取新闻信息
            return $this->articleInfo($param);
        }else if($url == 'article_edit'){ //编辑新闻
            return $this->articleEdit($param);
        }else if($url == 'article_del'){ //删除新闻
            return $this->articleDel($param);
        }else if($url == 'rotation_list'){ //轮播图列表
            return $this->rotationList($param);
        }else if($url == 'rotation_add'){ //添加轮播图
            return $this->rotationAdd($param);
        }else if($url == 'rotation_edit'){ //修改轮播图
            return $this->rotationEdit($param);
        }else if($url == 'rotation_del'){ //删除轮播图
            return $this->rotationDel($param);
        }else if($url == 'applet_info'){ //小程序设置参数
            return $this->appletInfo();
        }else if($url == 'applet_edit'){ //编辑小程序设置参数
            return $this->appletEdit($param);
        }else{
            return getJson(-1,"Api URL Error");
        }
    }

    //上传图片
    private function uploadPhoto($param)
    {
        if($_POST['type']==1){
            $path = 'news/newsIndexPhoto/';
        }if($_POST['type']==2){
        $path = 'news/navigatorPhoto/';
    }else{
        $path = 'news/newsPhoto/';
    }
        $upload = uploadPhoto($_FILES['file'],$path);
        $siteurl = ($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/file/'.$path;
        if($upload){
            $data = array(
                "src"  =>$siteurl .$upload
            );
            return getJson(200,$data);
        }
    }

    //用户列表
    private function userList($param)
    {
        $page = $param['page'] ? : 1;
        $limit = $param['limit'] ? : 1;
        $start=$limit*($page-1);//计算
        global $DB;
        $list = $DB->query("SELECT * FROM oreo_user order by id desc limit {$start},{$limit}")->fetchAll(PDO::FETCH_ASSOC);
        return getJson(200,$list);
    }

    //新闻列表
    private function articleList($param)
    {
        $page = $param['page'] ? : 1;
        $limit = $param['limit'] ? : 1;
        $start=$limit*($page-1);//计算
        global $DB;
        $list = $DB->query("SELECT * FROM oreo_article order by id desc limit  {$start},{$limit}")->fetchAll(PDO::FETCH_ASSOC);
        return getJson(200,$list);
    }

    //添加新闻
    private function articleAdd($param)
    {
        $time = date('Y-m-d');
        global $DB;
        $DB->exec("INSERT INTO `oreo_article` (`title`,`text`,`title_img`,`status`,`add_time`) VALUES ('{$param['news_title']}','{$param['news_text']}','{$param['newsLogo']}','{$param['status']}','{$time}')");
        return getJson(200,"添加成功");
    }

    //编辑新闻页面获取新闻信息
    private function articleInfo($param)
    {
        global $DB;
        $list[0] = $DB->query("SELECT * FROM oreo_article where id = {$param['news_id']}")->fetch(PDO::FETCH_ASSOC);
        return getJson(200,$list);
    }

    //编辑新闻
    private function articleEdit($param)
    {
        global $DB;
        //先删除旧图片数据
        if($param['newsLogo']){
            $list[0] = $DB->query("SELECT * FROM oreo_article where id = {$param['news_id']}")->fetch(PDO::FETCH_ASSOC);
            if($list[0]['title_img']!=$param['newsLogo']){
                $result = substr($list[0]['title_img'],strripos($list[0]['title_img'],"/file/")+1);
                unlink(ROOT.'/'.$result);//删除
            }
        }
        //写入数据库
        $DB->exec("update `oreo_article` set `title`='{$param['news_title']}',`text`='{$param['news_text']}',`title_img`='{$param['newsLogo']}',`status`='{$param['status']}' where `id`='{$param['news_id']}'");
        return getJson(200,"编辑成功");
    }

    //删除新闻
    private function articleDel($param)
    {
        global $DB;
        //先删除旧图片数据
        $list[0] = $DB->query("SELECT * FROM oreo_article where id = {$param['news_id']}")->fetch(PDO::FETCH_ASSOC);
        $result = substr($list[0]['title_img'],strripos($list[0]['title_img'],"/file/")+1);
        unlink(ROOT.'/'.$result);//删除
        $DB->exec("DELETE FROM oreo_article WHERE id='{$param['news_id']}' ");
        return getJson(200,"删除成功");
    }

    //轮播图列表
    private function rotationList($param)
    {
        $page = $param['page'] ? : 1;
        $limit = $param['limit'] ? : 1;
        $start=$limit*($page-1);//计算
        global $DB;
        $list=$DB->query("SELECT * FROM oreo_navigator order by id desc limit  {$start},{$limit}")->fetchAll(PDO::FETCH_ASSOC);
        return getJson(200,$list);
    }

    //添加轮播图
    private function rotationAdd($param)
    {
        $time = date('Y-m-d H:i:s');
        global $DB;
        $DB->exec("INSERT INTO `oreo_navigator` (`image_src`,`article_id`,`status`,`add_time`) VALUES ('{$param['image_src']}','{$param['news_id']}','{$param['status']}','{$time}')");
        return getJson(200,"添加成功");
    }

    //修改轮播图
    private function rotationEdit($param)
    {
        $time = date('Y-m-d H:i:s');
        global $DB;
        //先删除旧图片数据
        if($param['image_src']){
            $list[0] = $DB->query("SELECT * FROM oreo_navigator where id = {$param['navigator_id']}")->fetch(PDO::FETCH_ASSOC);
            if($list[0]['image_src']!=$param['image_src']){
                $result = substr($list[0]['image_src'],strripos($list[0]['image_src'],"/file/")+1);
                unlink(ROOT.'/'.$result);//删除
            }
        }
        $DB->exec("update `oreo_navigator` set `image_src`='{$param['image_src']}',`article_id`='{$param['article_id']}',`status`='{$param['status']}',`add_time`='{$time}' where `id`='{$param['navigator_id']}'");
        return getJson(200,"编辑成功");
    }

    //删除轮播图
    private function rotationDel($param)
    {
        global $DB;
        //首先获取
        $list[0] = $DB->query("SELECT * FROM oreo_navigator where id = {$param['navigator_id']}")->fetch(PDO::FETCH_ASSOC);
        $result = substr($list[0]['image_src'],strripos($list[0]['image_src'],"/file/")+1);
        unlink(ROOT.'/'.$result);//删除
        $DB->exec("DELETE FROM oreo_navigator WHERE id='{$param['navigator_id']}' ");
        return getJson(200,"删除成功");
    }

    //小程序设置参数
    private function appletInfo()
    {
        global $DB;
        $rs=$DB->query("select * from oreo_config");
        while($row=$rs->fetch()){
            $conf[$row['k']]=$row['v'];
        }
        return getJson(200,$conf);
    }

    //编辑小程序设置参数
    private function appletEdit($param)
    {
        global $DB;
        foreach ($param as $k => $value) {
            $DB->query("insert into oreo_config set `k`='{$k}',`v`='{$value}' on duplicate key update `v`='{$value}'");
        }
        return getJson(200,"编辑成功");
    }

}

$url = $_GET['api_uri'];
$oreo = new controller();
$oreo::setHeader();
echo $oreo->isRequestUrl($url);