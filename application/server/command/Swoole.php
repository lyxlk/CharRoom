<?php
/**
 * Created by PhpStorm.
 * User: KevinLin
 * Date: 2017/4/10
 * Time: 16:53
 * http 请求格式业务层代码格式： http://poker.com/index/swoole/server
 *
 * 服务器启动：
 * service nginx  restart
 * service redis  restart
 * service mysqld restart
 *
 * 查看nginx 所属组
 * ps aux | grep nginx
 * www ***** worker
 *
 * chmod -R 755 /var/www/html/poker
 * chown -R www.www /var/www/html/poker
 *
 * cli命令启动server： cd  /var/www/html/poker  &&  php think start
 *
 * shell 计划任务
 * # 每5分钟监控牌局server
 * /5 * * * * cd /var/www/html/poker && /usr/bin/php think swoole -m "monitor"
 * # 每小时执行一次 重启一下worker
 * 1 * * * *   cd /var/www/html/poker && /usr/bin/php think swoole -m "reload"
 *
 * swoole 总结
 * 当worker进程内发生致命错误或者人工执行exit时，进程会自动退出。
 * master进程会重新启动一个新的worker进程来继续处理请求
 *
 * 暴力杀死全部进程【不建议用于生成环境】
 * ps  aux | grep  Swoole_of_poker  | awk '{print $2}' | xargs kill -9
 *
 * #重启所有worker进程
 * kill -USR1 主进程PID
 *
 */
namespace app\server\command;

use app\index\model\Model_Keys;
use app\server\model\asyncTask;
use My\RedisPackage;
use think\console\Command;
use think\console\input\Option;
use My\Kit;
use think\console\Input;
use think\console\output;

class Swoole extends Command {

    protected $process_name   = "Swoole_of_chatRoom"; //当前进程名称
    protected $maste_pid_file = '/var/www/charRoom/runtime/swoole_master_pid.txt'; //保存当前进程pid

    public static $icons = [
        'https://lovepicture.nosdn.127.net/8814425931195142227?imageView&thumbnail=127y127&quality=85',
        'https://lovepicture.nosdn.127.net/-2938031258272153021?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/-3736890641936212495?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/-2529383531737213349?imageView&thumbnail=238y238&quality=85',
        'http://wenda.golaravel.com/uploads/avatar/000/00/29/78_avatar_max.jpg',
        'http://wenda.golaravel.com/uploads/avatar/000/00/00/38_avatar_max.jpg',
        'http://wenda.golaravel.com/uploads/avatar/000/00/36/75_avatar_max.jpg',
        'http://wenda.golaravel.com/uploads/avatar/000/00/01/00_avatar_max.jpg',
        'http://codeigniter.org.cn/forums/uc_server/images/noavatar_middle.gif',
        'http://codeigniter.org.cn/forums/uc_server/data/avatar/000/00/00/02_avatar_middle.jpg',
        'http://codeigniter.org.cn/forums/uc_server/avatar.php?uid=26093&size=middle',
        'http://codeigniter.org.cn/forums/uc_server/avatar.php?uid=46319&size=middle',
        'https://lovepicture.nosdn.127.net/-9214205956914410213?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/-1985404564932996838?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/1328010642000753089?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/1328010642000753089?imageView&thumbnail=238y238&quality=85',
        'http://img.hb.aicdn.com/70040c57efbe320c71d5abf64a5b10923149d690ee98-afSWwu_fw658',
        'http://img.hb.aicdn.com/a434fbae1ac89873718c9fc13ff4ebed133aaa5cc47c-zP3RwT_fw658',
        'https://pic.qqtn.com/up/2017-11/15106267764649076.jpg',
        'https://tvax4.sinaimg.cn/crop.4.0.504.504.180/68113a6fly8fn8a8om93jj20e80e0aaq.jpg',
        'https://lovepicture.nosdn.127.net/4239936184949743256?imageView&thumbnail=127y127&quality=85',
        'https://lovepicture.nosdn.127.net/-378823737510440156?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/-4848754629936768898?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/8492441991229681198?imageView&thumbnail=127y127&quality=85',
        'https://lovepicture.nosdn.127.net/626223253448272669?imageView&thumbnail=127y127&quality=85',
        'https://lovepicture.nosdn.127.net/-9220298945929913792?imageView&thumbnail=127y127&quality=85',
    ];

    protected $redis;
    protected $option_name = 'opt';

    protected $serv        = null;
    protected $swoole_ip   = "0.0.0.0";
    protected $swoole_port = 9654;

    protected $count_down_tick = 2;

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __construct() {
        parent::__construct();
    }

    /**
     * 设置计划任务名称
     */
    protected function configure() {
        $this->addOption($this->option_name, 'm', Option::VALUE_OPTIONAL, 'start'); //选项值必填

        //设置命令启动的名称 php think Swoole -m "start"
        $this->setName('Swoole')->setDescription('Here is the swoole server ');
    }

    /**
     * @param Input $input
     * @param output $output
     * 执行入口
     * shell 实现无人值守
     */
    protected function execute(Input $input, Output $output) {
        $options = $input->getOptions();
        if(isset($options[$this->option_name])) {
            switch ($options[$this->option_name]) {
                case "start"    : $this->start();break;
                case "reload"   : $this->reload();break;
                case "monitor"  : $this->monitor();break;
                case "stop"     : $this->stop();break;
                default : die("Usage:{start|stop|reload|monitor}");
            }
        } else {
            die("缺少必要参数");
        }
    }

    /**
     * 子进程重启
     * 建议每小时一次
     */
    public function reload() {
        $master_pid = intval(Kit::getConfig($this->maste_pid_file));
        if($master_pid) {
            $is_alive = \swoole_process::kill($master_pid, 0);
            if($is_alive === true) {

                exec("ps  aux | grep  {$this->process_name}  | awk '{print $2}'",$bpids);
                exec("kill -USR1 {$master_pid}",$retval, $status);
                exec("ps  aux | grep  {$this->process_name}  | awk '{print $2}'",$apids);

                //$status 0 是成功
                $debug  = "work reload Info >> before pids:".json_encode($bpids);
                $debug .= " |status:{$status}| Msg :".json_encode($retval,JSON_UNESCAPED_UNICODE);
                $debug .= " |after pids :".json_encode($apids);

                Kit::debug($debug,"server.reload");
            } else {
                Kit::debug("Server was not run","server.reload");
            }
        }
    }

    /**
     * 监控主进程状态
     * 建议5每分钟执行一次
     */
    public function monitor() {
        $master_pid = intval(Kit::getConfig($this->maste_pid_file));
        if($master_pid) {
            $is_alive = \swoole_process::kill($master_pid, 0);
            if($is_alive === false) {
                Kit::debug("Server Not Start Now starting...","server.monitor");
                $this->start();
            } else {
                Kit::debug("Server is running... pid is {$master_pid}","server.monitor");
            }
        } else {
            Kit::debug("master_pid not exist, now starting server...","server.monitor");
            $this->start();
        }
    }

    /**
     * @param string $msg
     * 强制杀死server所有进程
     */
    public function dangerKill($msg=''){
        //强制杀死
        exec("ps  aux | grep  {$this->process_name}  | awk '{print $2}' | xargs kill -9",$retval, $status);

        Kit::debug("{$msg} Now This is Dangers Option|status：{$status}|retval:".json_encode($retval),"server.stop.dangers");
    }

    /**
     * 安全停止 server
     */
    public function stop() {
        if(file_exists($this->maste_pid_file)) {
            $master_pid = intval(Kit::getConfig($this->maste_pid_file));
            if($master_pid) {
                $is_alive = \swoole_process::kill($master_pid, 0);
                if($is_alive === true) {
                    $flag = \swoole_process::kill($master_pid, SIGTERM);
                    $msg  = $flag ? "终止进程成功" : "终止进程失败！！！";
                    Kit::debug($msg,"server.stop");
                } else {
                    $msg = "stop Server false!!!";
                    $this->dangerKill($msg);
                }
            }
        } else {

            exec("ps  aux | grep  {$this->process_name}  | awk '{print $2}'",$bpids);

            if(!empty($bpids)) {
                $msg = "maste_pid_file not exist !!!!";
                $this->dangerKill($msg);
            }
        }
    }

    /**
     * 捕获Server运行期致命错误
     * https://wiki.swoole.com/wiki/page/305.html
     */
    public function handleFatal() {
        $error = error_get_last();
        $error = is_array($error) ? json_encode($error) : $error;
        Kit::debug($error,'handleFatal');
    }

    /**
     * 开启 server
     */
    public function start() {
        \swoole_set_process_name($this->process_name);

        //\swoole_server 加反斜杠 表示当前类不在当前的命名空间内
        $this->serv = new \swoole_websocket_server($this->swoole_ip, $this->swoole_port);
        $this->serv->set(array(
            'reactor_num' => 2, //通过此参数来调节poll线程的数量，以充分利用多核
            'daemonize' => true, //加入此参数后，执行php server.php将转入后台作为守护进程运行,ps -ef | grep {this->process_name}
            'worker_num' => 3,//worker_num配置为CPU核数的1-4倍即可
            'dispatch_mode' => 2,//https://wiki.swoole.com/wiki/page/277.html
            'max_request' => 100,//此参数表示worker进程在处理完n次请求后结束运行，使用Base模式时max_request是无效的
            'backlog' => 128,   //此参数将决定最多同时有多少个待accept的连接，swoole本身accept效率是很高的，基本上不会出现大量排队情况。
            'log_level' => 5,//https://wiki.swoole.com/wiki/page/538.html
            'log_file' => '/var/www/charRoom/runtime/log_file.'.date("Ym").'.txt',// https://wiki.swoole.com/wiki/page/280.html 仅仅是做运行时错误记录，没有长久存储的必要。
            'heartbeat_check_interval' => 30, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
            'heartbeat_idle_time' => 3600, //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过heartbeat_idle_time会把这个连接关闭。
            'task_worker_num' => 2,
            'pid_file'=> $this->maste_pid_file,//kill -SIGUSR1 $(cat server.pid)  重启所有worker进程
            'task_max_request' => 1000,//设置task进程的最大任务数，一个task进程在处理完超过此数值的任务后将自动退出，防止PHP进程内存溢出
            'user'  => 'apache',
            'group' => 'apache',
            //'chroot' => '/tmp/root'
            'open_eof_split' => true,
            'package_eof' => "\r\n"
        ));

        $this->serv->on('open', array(&$this,'pokerOpen'));

        $this->serv->on('message', array($this,'pokerReceive'));

        $this->serv->on('Task', array(&$this,'pokerTask'));//处理异步任务

        $this->serv->on('Finish', array(&$this,'pokerFinish'));//有Task就得有Finish

        $this->serv->on('WorkerStart', array(&$this,'pokerWorkerStart'));//定时器

        $this->serv->on('close', array(&$this,'pokerClose'));

        //https://wiki.swoole.com/wiki/page/19.html
        $this->serv->start();//启动成功后会创建worker_num+2个进程

    }

    /**
     * @param $fd
     * @return mixed|string
     * 更具FD 获取头像
     */
    public static function getIconByFd($fd) {
        $icon = isset(self::$icons[intval($fd) % count(self::$icons)]) ? self::$icons[intval($fd) % count(self::$icons)] : "";
        return $icon;
    }

    /**
     * @param $serv
     * @param $frame
     * 客户端连接服务器
     */
    public function pokerOpen($serv, $frame) {
        //socket链接成功
        $icon = self::getIconByFd($frame->fd);
        $serv->push($frame->fd,Kit::json_response(100,'ok',['icon'=>$icon,'fd'=>$frame->fd]));
    }

    /**
     * @param $serv
     * @param $frame
     * 消息中心
     */
    public function pokerReceive($serv, $frame) {
        try {
            $key   = Model_Keys::pokerReceive($frame->fd);
            $redis = new RedisPackage([],$serv->worker_id);

            if(!$redis->SETNX($key,1)) {
                $redis->expire($key,5);
                $serv->push($frame->fd,Kit::json_response(1,'ok',[
                    'msg'  =>'服务器消息：别瞎搞',
                    'icon' =>"http://pics.sc.chinaz.com/Files/pic/icons128/5938/i6.png",
                    'fd'   =>$frame->fd,
                ]));
            } else {
                $redis->expire($key,1);
                $data = strval($frame->data);
                $data = mb_strlen($data,'utf-8') > 30 ? mb_substr($data,0,30) : $data;
                if(!empty($data)) {
                    $serv->task(json_encode(['msg'=>$data,'fd'=>$frame->fd]));
                    $icon = self::getIconByFd($frame->fd);
                    foreach($serv->connections as $fd) {
                        $serv->push($fd,Kit::json_response(1,'ok',[
                            'msg'=>$data,
                            'icon'=>$icon,
                            'fd'=>$frame->fd,
                        ]));
                    }
                }
            }
        } catch(\Exception $e) {
            $err_msg = $e->getMessage(). '==='. $e->getFile(). '==>'. $e->getLine();
            Kit::debug($err_msg,'pokerReceive_err');
            die(-1);
        }
    }

    /**
     * @param $serv
     * @param $fd
     * 客户端链接关闭回掉
     */
    public function pokerClose($serv, $fd) {

    }

    /**
     * User: KevinLin
     * @param $serv
     * @param $task_id
     * @param $data
     * @return bool
     * 1.7.2以上的版本，在onTask函数中 return字符串，表示将此内容返回给worker进程。
     * worker进程中会触发onFinish函数，表示投递的task已完成。
     */
    public function pokerTask($serv, $task_id, $src_worker_id, $data) {
        return asyncTask::LogToDb($serv, $task_id, $src_worker_id, $data,1);
    }

    public function pokerFinish($serv, $task_id, $data) {

    }

    /**
     * @param $serv
     * @param $worker_id
     * tick定时器
     */
    public function pokerWorkerStart($serv, $worker_id) {

    }
}
