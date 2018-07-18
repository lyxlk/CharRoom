<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 2018/7/5
 * Time: 15:18
 */
namespace app\index\controller;
use app\server\command\Swoole;
use app\server\model\asyncTask;
use My\Kit;
use think\Controller;
use think\View;

class Index extends Controller {

    public function index() {
        $msg = json_encode(['msg'=>"【用户登陆】|CLASS:".__CLASS__."|Func:".__FUNCTION__,'fd'=>0]);
        asyncTask::LogToDb(null, null, null, $msg);
        $view = new View();
        return $view->fetch('index');
    }

    public function shake() {
        if( $this->request->isAjax()) {
            try {
                $PlayerLogObj = asyncTask::getDbObj();
                //百分之60的概率获取一条记录
                if(mt_rand(0,100) <= 60) {
                    return Kit::json_response(-1,'');
                }

                list($status,$ret) = $PlayerLogObj->getOneLog();
                if(!$status) {
                    echo Kit::json_response(-1,'',null,true);
                }

                $icon = Swoole::getIconByFd($ret['fd']);
                echo Kit::json_response(1,'ok',['icon'=>$icon,'msg'=>$ret['msg']],true);
            }catch (\Exception $e) {
                Kit::json_response(-1,$e->getMessage());
            }
        } else {
            $msg = json_encode(['msg'=>"【摇一摇】|CLASS:".__CLASS__."|Func:".__FUNCTION__,'fd'=>0]);
            asyncTask::LogToDb(null, null, null, $msg);

            $view = new View();
            return $view->fetch('shake');
        }

    }
}