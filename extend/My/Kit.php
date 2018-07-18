<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2018/1/11
 * Time: 15:32
 */
namespace My;
class Kit {
    /**
     * @desc    检测是否为html5版本
     */
    public static function checkHtml5($get){
        return isset($get['client']) && $get['client'] == '0x5f00' ? TRUE : FALSE;
    }

    /**
     * User: KevinLin
     * @param $url
     * @param array $post_data : post的数据,为空时表示get请求
     * @param int $timeout
     * @param bool $getinfo
     * @param array $header
     * @return array|mixed
     * 抓取网页
     */
    public static function curl($url, $post_data = array(), $timeout = 9, $getinfo = false, $header=array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if (!empty($post_data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }

        $result = curl_exec($ch);
        if ($getinfo) {
            $_getinfo = curl_getinfo($ch);
        }

        curl_close($ch);

        if ($getinfo) {
            return array(
                'getinfo' => $_getinfo,
                'response' => $result,
            );
        }

        return $result;
    }


    /**
     * 统一输出接口
     *
     * @param int $status 返回状态
     * @param null $msg 返回信息
     * @param null $data 返回数据
     * @param bool $exit 是否终止
     * @param bool $only_data  true:返回的数据格式不按照status/msg/data的形式走，直接返回$data原数据
     * @return mixed
     *
     */
    public static function json_response($status = 1, $msg = null, $data = null, $exit = false, $only_data = false) {

        if (!$only_data) {
            $array = array(
                'status' => $status,
            );

            if ($msg) {
                $array = array_merge($array, array('msg' => $msg));
            }

            if ($data) {
                $array = array_merge($array, array('data' => $data));
            }
        } else {
            $array = $data;
        }

        if (self::checkHtml5($_GET)) {
            if (isset($_GET['callback'])) {
                $callback = filter_var($_GET['callback'], FILTER_SANITIZE_STRING);
            } else {
                $callback = filter_var('jQuery_html5_poker_error', FILTER_SANITIZE_STRING);
            }
            return self::getBytes( $callback . '(' . json_encode($array) . ');' );
        } else {
            // 返回数据进行压缩处理，目前支持base64和zlib两种处理
            $returnStr = json_encode($array);
            $ioFilters = isset($_SERVER['HTTP_IO_FILTERS']) && !empty($_SERVER['HTTP_IO_FILTERS'])
                ? trim($_SERVER['HTTP_IO_FILTERS']) : '';

            if ($ioFilters == 'base64')
            {
                header("io-filters: {$_SERVER['HTTP_IO_FILTERS']}");
                $endStr = base64_encode($returnStr);
                return self::getBytes( $endStr );
            }
            else if($ioFilters == 'zlib')
            {

                header("io-filters: {$_SERVER['HTTP_IO_FILTERS']}");
                $endStr = gzcompress($returnStr);
                return self::getBytes( $endStr );
            }
            else
            {
                return self::getBytes( $returnStr );
            }
        }

    }


    /**

     * 转换一个String字符串为byte数组

     * @param $str 需要转换的字符串

     * @param $bytes 目标byte数组

     * @author Zikie

     */
    public static function getBytes($string) {
        /* $bytes = array();
         for($i = 0; $i < strlen($string); $i++){
             $bytes[] = ord($string[$i]);
         }*/
        //return ord($string);
        return ($string);//暂时不发二进制
    }


    /**

     * 将字节数组转化为String类型的数据

     * @param $bytes 字节数组

     * @param $str 目标字符串

     * @return 一个String类型的数据

     */

    public static function toStr($bytes) {
        $str = '';
        foreach($bytes as $ch) {
            $str .= chr($ch);
        }

        return $str;
    }


    /**
     * @param $dir
     * @return bool
     * 递归创建目录
     */
    public static function create_folders($dir){
        return is_dir($dir) or (self::create_folders(dirname($dir)) and mkdir($dir, 0777,true));
    }



    /**
     * @param string $string
     * @param string $filename
     * 打印错误日志
     */
    public static function debug($string='',$filename='',$opt=FILE_APPEND) {
        $filename = $filename ? $filename : "chart_room";
        $filename = '/var/www/charRoom/runtime/swoole_debug/'.date("Ymd")."/".$filename.".txt";
        $string   = date("Y-m-d H:i:s")."|".strval($string)."\n\t";
        $dirname  = dirname($filename);
        if(!is_dir($dirname)){
            $oldmask = umask(0);
            mkdir($dirname,0777,true);
            umask($oldmask);
        }

        file_put_contents($filename,$string,$opt);

        //异步文件系统IO
        //todo https://wiki.swoole.com/wiki/page/185.html
        //swoole_async_writefile($filename, $string, null, $opt);

        chown($filename,'apache');
    }

    public static function getConfig($filename='') {
        if(!file_exists($filename)) {
            return false;
        }

        return file_get_contents($filename);
    }
}