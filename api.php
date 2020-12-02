<?php
include './include/oreo.cron.php';
header('content-type:application/json;charset=utf-8');

class api
{

    //函数选择器
    public function isRequestUrl($url, $param)
    {
        //用户登录
        if($url == 'user_login'){
            return $this->userOpenId($param);
        }else if($url == 'user_info'){ //写入用户信息
            return $this->setUserInfo($param);
        }else if($url == 'rotation_chart'){ //获取首页轮播图
            return $this->rotationChart();
        }else if($url == 'news_info'){ //获取新闻
            return $this->newsInfo();
        }else if($url == 'article_info'){ //获取对应ID的新闻
            return $this->articleInfo($param);
        }else if($url == 'collection_news_info'){ //收藏检测
            return $this->collectionNewsInfo($param);
        }else if($url == 'collection_news_add'){ //收藏添加
            return $this->collectionAdd($param);
        }else if($url == 'collection_news_del'){ //收藏取消
            return $this->collectionDel($param);
        }else if($url == 'user_article'){ //用户收藏的新闻
            return $this->userArticle($param);
        }else if($url == 'navigation_Bar'){ //获取导航条信息
            return $this->navigationBar();
        }else{
            return getJson(-1,"Api URL Error");
        }
    }

    //用户登录
    private function userOpenId($param)
    {
        include_once(INCLUDE_ROOT."WechatAuth.php");
        $wx = new WechatAuth(); //初始化类
        $wx->appId = '公众号appId'; //公众号appId
        $wx->appSecret = '公众号appSecret'; //公众号appSecret
        $wx->code = $param['code']; //微信小程序登录时获取的code
        $arr = $wx->authIndex(3);//调用函数
        $arr = json_decode($arr,true);
        //将会获取到
        $openid = $arr['openid']; //用户openId
        //如果获取到OpenID
        if($openid){
            global $DB;
            //查询是否存在
            $user = $DB->query("select * from oreo_user where user_openid='{$openid}'")->fetch();
            if(empty($user)){
                $time = date('Y-m-d H:i:s');
                //写入数据库
                $DB->exec("INSERT INTO `oreo_user` (`user_openid`,`add_time`) VALUES ('{$openid}','{$time}')");
            }
            return getJson(200,$openid);
        }
    }

    //写入用户信息
    private function setUserInfo($param)
    {
        $openId =  preg_replace('/[ ]/','',$param['openId']);
        if(!$openId){
            exit(getJson(-1,'openId不能为空'));
        }
        $nickName = $param['nickName'] ?  : 1;
        $gender = $param['gender'] ?  : 1;
        $avatarUrl = $param['avatarUrl'] ?  : 1;
        $city = $param['city'] ?  : 1;
        $province = $param['province'] ?  : 1;
        $country = $param['country'] ?  : 1;
        //写入数据库
        global $DB;
        $DB->exec("update `oreo_user` set `user_nickname`='{$nickName}',`user_sex`='{$gender}',`user_headimgurl`='{$avatarUrl}',`user_city`='{$city}',`user_province`='{$province}',`user_country`='{$country}' where `user_openid`='$openId'");
        return getJson(200,$openId);
    }

    //获取首页轮播图
    private function rotationChart()
    {
        global $DB;
        $list=$DB->query("SELECT * FROM oreo_navigator where status ='1' order by id desc ")->fetchAll(PDO::FETCH_ASSOC);
        return getJson(200,$list);
    }

    //获取新闻
    private function newsInfo()
    {
        global $DB;
        $list=$DB->query("SELECT * FROM oreo_article where status = 1 order by id desc ")->fetchAll(PDO::FETCH_ASSOC);
        return getJson(200,$list);
    }

    //获取对应ID的新闻
    private function articleInfo($param)
    {
        global $DB;
        $newsId =  preg_replace('/[ ]/','',$param['newsId']);
        if(!$newsId){
            return getJson(-1,'参数错误');
        }
        $list=$DB->query("select * from oreo_article where id='{$newsId}'")->fetch(PDO::FETCH_ASSOC);
        return getJson(200,$list);
    }

    //收藏检测
    private function collectionNewsInfo($param)
    {
        $openId = preg_replace('/[ ]/', '', $param['openId']);
        if (!$openId) {
            return getJson(-1,'openId不能为空');
        }
        if (!$param['newsId']) {
            return getJson(-1,'newsId不能为空');
        }
        //查询是否存在
        global $DB;
        $newsInfo = $DB->query("select * from oreo_collect where user_openid='{$openId}' and article_id='{$param['newsId']}' ")->fetch(PDO::FETCH_ASSOC);
        if($newsInfo){
            return getJson(-1,'您已经收藏过哟');
        }else{
            return getJson(200,'未收藏');
        }
    }

    //收藏添加
    private function collectionAdd($param)
    {
        $openId = preg_replace('/[ ]/', '', $param['openId']);
        if (!$openId) {
            return getJson(-1,'openId不能为空');
        }
        if (!$param['newsId']) {
            return getJson(-1,'newsId不能为空');
        }
        //查询是否存在
        global $DB;
        $newsInfo = $DB->query("select * from oreo_collect where user_openid='{$openId}' and article_id='{$param['newsId']}' ")->fetch(PDO::FETCH_ASSOC);
        if(empty($newsInfo)){
            //写入数据库
            $time = date('Y-m-d');
            $DB->exec("INSERT INTO `oreo_collect` (`user_openid`,`article_id`,`collect_time`) VALUES ('{$openId}','{$param['newsId']}','{$time}')");
            return getJson(200,'收藏成功');
        }else{
            return getJson(-1,'您已经收藏过哟');
        }
    }

    //收藏取消
    private function collectionDel($param)
    {
        $openId = preg_replace('/[ ]/', '', $param['openId']);
        if (!$openId) {
            return getJson(-1,'openId不能为空');
        }
        if (!$param['newsId']) {
            return getJson(-1,'newsId不能为空');
        }
        //查询是否存在
        global $DB;
        $newsInfo = $DB->query("select * from oreo_collect where user_openid='{$openId}' and article_id='{$param['newsId']}' ")->fetch(PDO::FETCH_ASSOC);
        if(empty($newsInfo)){
            return getJson(-1,'未收藏');
        }else{
            $DB->exec("DELETE FROM oreo_collect WHERE user_openid='{$openId}' and article_id='{$param['newsId']}'");
            return getJson(200,'已取消收藏');
        }
    }

    //用户收藏的新闻
    private function userArticle($param)
    {
        $openId =  preg_replace('/[ ]/','',$param['openId']);
        if(!$openId){
            return getJson(-1,'openId不能为空');
        }
        global $DB;
        $list=$DB->query("select * from oreo_collect where user_openid='{$openId}'")->fetchAll(PDO::FETCH_ASSOC);
        $data = array_unique(array_column($list,'article_id'));
        $id = implode(',',$data);
        $myList = $DB->query("select * from oreo_article where id in ({$id}) ")->fetchAll(PDO::FETCH_ASSOC);
        return getJson(200,$myList);
    }

    //获取导航条信息
    private function navigationBar()
    {
        global $DB;
        $rs=$DB->query("select * from oreo_config");
        while($row=$rs->fetch()){
            $conf[$row['k']]=$row['v'];
        }
        return getJson(200,$conf);
    }

}

$url = $_GET['api_uri'];
$oreo = new api();
echo $oreo->isRequestUrl($url, $_POST);