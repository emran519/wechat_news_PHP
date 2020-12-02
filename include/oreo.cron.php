<?php
error_reporting(0); //关闭错误
define('INCLUDE_ROOT', dirname(__FILE__).'/'); //设置INCLUDE_ROOT(include目录)
define('ROOT', dirname(INCLUDE_ROOT).'/'); //定义ROOT目录(根目录)
require INCLUDE_ROOT.'oreo.config.php';//引入数据库文件
/**
 * 开始连接数据库
 */
if(!defined('SQLITE') && (!$sql_config['user']||!$sql_config['pwd']||!$sql_config['dbname']))//检测安装
{
    exit('请先导入SQL文件，确保系统能够正常读取数据库文件');
}try {
    //mysql PDO连接
    $DB = new PDO("mysql:host={$sql_config['host']};dbname={$sql_config['dbname']};port={$sql_config['port']}",$sql_config['user'],$sql_config['pwd']);
}catch(Exception $e){
    exit('链接数据库失败:'.$e->getMessage());
}

/**
 * 返回JSON函数
 */
function getJson( $code = 0, $data){
    if($code==200) {
        $arr['code'] = $code;
        $arr['msg'] = '成功';
        $arr['data'] = $data;
    }else{
        $arr['code'] = -1;
        $arr['msg'] = '失败';
        $arr['data'] = $data;
    }
    return json_encode($arr);
}

/**
 * 上传图片
 */
function uploadPhoto($file,$path){
    $fileEx=strtolower(substr(strrchr($file["name"],"."),1));
    $file_name = date("YmdHis").mt_rand(11111,99999).'.'.$fileEx;
//定义存储文件名
    $file_path = ROOT."/file/".$path.$file_name;
//文件存储路径
    if (move_uploaded_file($file["tmp_name"],$file_path)){
        //对上传的文件进行判断是否上传成功
       return $file_name;
    }else{
        return false;
    }
}