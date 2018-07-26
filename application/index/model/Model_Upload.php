<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 2018/7/20
 * Time: 15:29
 */
namespace app\index\model;

use think\Model;

class Model_Upload extends Model {
    public static $allow_type = ['jpg','jpeg','gif','png']; //定义允许上传的类型

    /**
     * @param $file : 二进制文件 (Content-Type: multipart/form-data)
     * @param array $allow_type : 允许类型
     * @param string $path
     * @return array
     * 上图片至本地服务器
     */
    public static function uploadToLocal($file,$allow_type=[],$path='') {
        try {
            if(empty($file)) {
                throw new \Exception("图片未上传");
            }

            //判断是否是通过HTTP POST上传的
            if(!is_uploaded_file($file['tmp_name'])){
                throw new \Exception("仅允许HTTP POST上传");
            }
            $size = intval(sprintf('%.2f',$file['size'] / (1024 *1024)));

            if($size > 1) {
                throw new \Exception("图片最大不超过1~2M<br /> 压缩图片地址：<a href='https://tinypng.com/' target='_blank'>https://tinypng.com/</a>");
            }

            $name  = $file['name'];
            $valid = @getimagesize($file['tmp_name']);
            if($valid === false) {
                throw new \Exception("文件格式错误");
            }


            $type       = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
            $allow_type = empty($allow_type) ? self::$allow_type : $allow_type;
            if(!in_array($type, $allow_type)){
                throw new \Exception("非法的文件类型：{$type};仅允许'jpg','jpeg','gif','png' 格式");
            }

            //开始移动文件到相应的文件夹
            $path = $path ? $path : WEB_PATH.UPLOAD_SYS_PATH.'/'.date("Ym");
            if(!is_dir($path) ) {
                $oldumask = umask(0);
                mkdir($path,0777,true);
                umask($oldumask);
            }

            //新文件名
            $object = uniqid().mt_rand(10,99).'.'.$type;

            $upload_file = $path.'/'.$object; //上传文件
            if(move_uploaded_file($file['tmp_name'],$upload_file)){
                //服务器相对目录
                $private_path = UPLOAD_SYS_PATH.'/'.date("Ym").'/'.$object;
                return [true, $private_path];
            } else {
                throw new \Exception("图片上传失败");
            }
        } catch (\Exception $e) {
            return [false,$e->getMessage()];
        }
    }
}