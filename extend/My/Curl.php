<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace My;

class Curl
{
   public $options =[];

   public function get($url)
   {
       $curl = curl_init();

       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_TIMEOUT, 500);
       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
       curl_setopt($curl, CURLOPT_URL, $url);
       foreach($this->options as $k => $v){
           curl_setopt($curl, $k, $v);
       }

       $res = curl_exec($curl);
       curl_close($curl);

       return $res;
   }

   public function post($url,$post)
   {
       $curl = curl_init();

       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_TIMEOUT, 500);
       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
       curl_setopt($curl, CURLOPT_URL, $url);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
       curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
       foreach($this->options as $k => $v){
           curl_setopt($curl, $k, $v);
       }
       $res = curl_exec($curl);
       curl_close($curl);

       return $res;
   }

}
