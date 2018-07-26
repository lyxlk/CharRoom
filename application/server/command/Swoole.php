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
use My\Randomname;
use My\RedisPackage;
use think\console\Command;
use think\console\input\Option;
use My\Kit;
use think\console\Input;
use think\console\output;
use think\Cookie;

class Swoole extends Command {

    protected $process_name   = "Swoole_of_chatRoom"; //当前进程名称
    protected $master_pid_file = '/var/www/charRoom/runtime/swoole_master_pid.txt'; //保存当前进程pid
    public static $md5Key = "1311552030@qq.com";//自定义一个签名，我这里乱填的

    public static $icons = [
        'https://lovepicture.nosdn.127.net/8814425931195142227?imageView&thumbnail=127y127&quality=85',
        'https://lovepicture.nosdn.127.net/-2938031258272153021?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/-3736890641936212495?imageView&thumbnail=238y238&quality=85',
        'https://lovepicture.nosdn.127.net/421243273678009447?imageView',
        'https://lovepicture.nosdn.127.net/3255199884390267295?imageView',
        'https://lovepicture.nosdn.127.net/7259506587830317577?imageView',
        'https://lovepicture.nosdn.127.net/8206724380878302162?imageView',
        'https://lovepicture.nosdn.127.net/-4344300205786863478?imageView',
        'https://lovepicture.nosdn.127.net/-7979581211781744711?imageView',
        'https://lovepicture.nosdn.127.net/4697127335281528040?imageView',
        'https://lovepicture.nosdn.127.net/8318259750208461078?imageView',
        'https://lovepicture.nosdn.127.net/1810396337464327332?imageView',
        'https://lovepicture.nosdn.127.net/367036025462625845?imageView',
        'https://lovepicture.nosdn.127.net/-1602426767614710336?imageView',
        'https://lovepicture.nosdn.127.net/-8592386226288078264?imageView',
        'https://lovepicture.nosdn.127.net/-6799925296984499706?imageView',
        'https://lovepicture.nosdn.127.net/8688181059073330117?imageView',
        'https://lovepicture.nosdn.127.net/807320746298592423?imageView',
        'https://lovepicture.nosdn.127.net/-1850163430969686743?imageView',
        'https://lovepicture.nosdn.127.net/4546068045856877285?imageView',
        'https://lovepicture.nosdn.127.net/5516051328345897263?imageView',
        'https://lovepicture.nosdn.127.net/-2620003841988132266?imageView',
        'https://lovepicture.nosdn.127.net/-7448531318866898867?imageView',
        'https://lovepicture.nosdn.127.net/1022382986073067096?imageView',
        'https://lovepicture.nosdn.127.net/-3285471731439789806?imageView',
        'https://lovepicture.nosdn.127.net/2402064079405554389?imageView',
        'https://lovepicture.nosdn.127.net/3781815285890242230?imageView',
        'https://lovepicture.nosdn.127.net/8904959701116805236?imageView',
        'https://lovepicture.nosdn.127.net/7181463171447212063?imageView',
        'https://lovepicture.nosdn.127.net/3347319654616881822?imageView',
        'https://lovepicture.nosdn.127.net/-4983158765513899746?imageView',
        'https://lovepicture.nosdn.127.net/2794474067316903665?imageView',
        'https://lovepicture.nosdn.127.net/7285497098702404782?imageView',
        'https://lovepicture.nosdn.127.net/-1671303542733565692?imageView',
        'https://lovepicture.nosdn.127.net/-1564193159908095003?imageView',
        'https://lovepicture.nosdn.127.net/-9216418357521891141?imageView',
        'https://lovepicture.nosdn.127.net/4324189126872934834?imageView',
        'https://lovepicture.nosdn.127.net/8462442780971033575?imageView',
        'https://lovepicture.nosdn.127.net/-6421855798778513627?imageView',
        'https://lovepicture.nosdn.127.net/925580096079463952?imageView',
        'https://lovepicture.nosdn.127.net/7997611500798244997?imageView',
        'https://lovepicture.nosdn.127.net/6101888039067777032?imageView',
        'https://lovepicture.nosdn.127.net/-7696617157115197256?imageView',
        'https://lovepicture.nosdn.127.net/-2799301563911425952?imageView',
        'https://lovepicture.nosdn.127.net/1136783182376835849?imageView',
        'https://lovepicture.nosdn.127.net/4581239960405619804?imageView',
        'https://lovepicture.nosdn.127.net/8830682116895681784?imageView',
        'https://lovepicture.nosdn.127.net/9150035178683953710?imageView',
        'https://lovepicture.nosdn.127.net/-8649069678326103978?imageView',
        'https://lovepicture.nosdn.127.net/747009516106271319?imageView',
        'https://lovepicture.nosdn.127.net/-6893270152831801755?imageView',
        'https://lovepicture.nosdn.127.net/2512225557882185072?imageView',
        'https://lovepicture.nosdn.127.net/-6758659703351107851?imageView',
        'https://lovepicture.nosdn.127.net/7473278334881456547?imageView',
        'https://lovepicture.nosdn.127.net/-957081019825454751?imageView',
        'https://lovepicture.nosdn.127.net/-7648336831480492650?imageView',
        'https://lovepicture.nosdn.127.net/8381802155433290214?imageView',
        'https://lovepicture.nosdn.127.net/505926086162499554?imageView',
        'https://lovepicture.nosdn.127.net/1539182332689549260?imageView',
        'https://lovepicture.nosdn.127.net/-4682449277768031978?imageView',
        'https://lovepicture.nosdn.127.net/709465392526717173?imageView',
        'https://lovepicture.nosdn.127.net/-5774302127381556046?imageView',
        'https://lovepicture.nosdn.127.net/3958469809970219979?imageView',
        'https://lovepicture.nosdn.127.net/177481597902362917?imageView',
        'https://lovepicture.nosdn.127.net/-2042028038944494940?imageView',
        'https://lovepicture.nosdn.127.net/7586601309751819211?imageView',
        'https://lovepicture.nosdn.127.net/-8427470710130600080?imageView',
        'https://lovepicture.nosdn.127.net/4931280063170963688?imageView',
        'https://lovepicture.nosdn.127.net/-8553801123610617498?imageView',
        'https://lovepicture.nosdn.127.net/3195743135710007220?imageView',
        'https://lovepicture.nosdn.127.net/-6999234130506221931?imageView',
        'https://lovepicture.nosdn.127.net/-7639601465754650268?imageView',
        'https://lovepicture.nosdn.127.net/4686405982646928497?imageView',
        'https://lovepicture.nosdn.127.net/-631018497656681268?imageView',
        'https://lovepicture.nosdn.127.net/-6172609985704151011?imageView',
        'https://lovepicture.nosdn.127.net/-9039560913184348316?imageView',
        'https://lovepicture.nosdn.127.net/-2921973453862927924?imageView',
        'https://lovepicture.nosdn.127.net/8539627778752217550?imageView',
        'https://lovepicture.nosdn.127.net/3624469987476862458?imageView',
        'https://lovepicture.nosdn.127.net/2937578067770641169?imageView',
        'https://lovepicture.nosdn.127.net/-4649548906030987120?imageView',
        'https://lovepicture.nosdn.127.net/6478191549580253281?imageView',
        'https://lovepicture.nosdn.127.net/-6792953631022122164?imageView',
        'https://lovepicture.nosdn.127.net/1315377432218293875?imageView',
        'https://lovepicture.nosdn.127.net/2286911497488254637?imageView',
        'https://lovepicture.nosdn.127.net/-7621186243055603198?imageView',
        'https://lovepicture.nosdn.127.net/-5939693684089487459?imageView',
        'https://lovepicture.nosdn.127.net/-5983958652974214500?imageView',
        'https://lovepicture.nosdn.127.net/4086954907445243758?imageView',
        'https://lovepicture.nosdn.127.net/4790251807565304185?imageView',
        'https://lovepicture.nosdn.127.net/-8010539877818302691?imageView',
        'https://lovepicture.nosdn.127.net/74246391420216281?imageView',
        'https://lovepicture.nosdn.127.net/6978098326722179243?imageView',
        'https://lovepicture.nosdn.127.net/8480047123196554117?imageView',
        'https://lovepicture.nosdn.127.net/4915490702444738532?imageView',
        'https://lovepicture.nosdn.127.net/2364105342027182189?imageView',
        'https://lovepicture.nosdn.127.net/8320951241340408318?imageView',
        'https://lovepicture.nosdn.127.net/-3197985704246147584?imageView',
        'https://lovepicture.nosdn.127.net/7490834780936640531?imageView',
        'https://lovepicture.nosdn.127.net/4089029754002420312?imageView',
        'https://lovepicture.nosdn.127.net/-132481038518985230?imageView',
        'https://lovepicture.nosdn.127.net/5978759617736982313?imageView',
        'https://lovepicture.nosdn.127.net/-3984059394739144885?imageView',
        'https://lovepicture.nosdn.127.net/9130907320819694092?imageView',
        'https://lovepicture.nosdn.127.net/-3104566079701706838?imageView',
        'https://lovepicture.nosdn.127.net/8609121592172918681?imageView',
        'https://lovepicture.nosdn.127.net/5941201847785529980?imageView',
        'https://lovepicture.nosdn.127.net/796293748683704871?imageView',
        'https://lovepicture.nosdn.127.net/-5782366838002939075?imageView',
        'https://lovepicture.nosdn.127.net/3873982018789950626?imageView',
        'https://lovepicture.nosdn.127.net/2037635339446425441?imageView',
        'https://lovepicture.nosdn.127.net/8641124205714052552?imageView',
        'https://lovepicture.nosdn.127.net/3829356384292508805?imageView',
        'https://lovepicture.nosdn.127.net/-7737761790901539321?imageView',
        'https://lovepicture.nosdn.127.net/386460679735511153?imageView',
        'https://lovepicture.nosdn.127.net/1108799505152631594?imageView',
        'https://lovepicture.nosdn.127.net/5804509239347959909?imageView',
        'https://lovepicture.nosdn.127.net/-230211871978808289?imageView',
        'https://lovepicture.nosdn.127.net/-8343836475015480225?imageView',
        'https://lovepicture.nosdn.127.net/2498492210913749998?imageView',
        'https://lovepicture.nosdn.127.net/-50703201534428740?imageView',
        'https://lovepicture.nosdn.127.net/2145787677918782453?imageView',
        'https://lovepicture.nosdn.127.net/-6450227150741602001?imageView',
        'https://lovepicture.nosdn.127.net/6444459594949921994?imageView',
        'https://lovepicture.nosdn.127.net/-8023205706584692820?imageView',
        'https://lovepicture.nosdn.127.net/-2693606364953359179?imageView',
        'https://lovepicture.nosdn.127.net/3233870955379790350?imageView',
        'https://lovepicture.nosdn.127.net/-3067416345943547348?imageView',
        'https://lovepicture.nosdn.127.net/6940679386050799708?imageView',
        'https://lovepicture.nosdn.127.net/4300629713048602071?imageView',
        'https://lovepicture.nosdn.127.net/-3751855867841109410?imageView',
        'https://lovepicture.nosdn.127.net/4373798085847101576?imageView',
        'https://lovepicture.nosdn.127.net/-8291911889767273252?imageView',
        'https://lovepicture.nosdn.127.net/8097460381828751436?imageView',
        'https://lovepicture.nosdn.127.net/8021716512696727615?imageView',
        'https://lovepicture.nosdn.127.net/2670773201586610754?imageView',
        'https://lovepicture.nosdn.127.net/-1932190821009975353?imageView',
        'https://lovepicture.nosdn.127.net/5876865162947290254?imageView',
        'https://lovepicture.nosdn.127.net/3053914104952785165?imageView',
        'https://lovepicture.nosdn.127.net/-7473110269891938512?imageView',
        'https://lovepicture.nosdn.127.net/-8423364926281871195?imageView',
        'https://lovepicture.nosdn.127.net/-4484550686478886163?imageView',
        'https://lovepicture.nosdn.127.net/1597537970438231421?imageView',
        'https://lovepicture.nosdn.127.net/-1454489758228052217?imageView',
        'https://lovepicture.nosdn.127.net/-2854934299622605420?imageView',
        'https://lovepicture.nosdn.127.net/-5936446531840632141?imageView',
        'https://lovepicture.nosdn.127.net/8891503850432222531?imageView',
        'https://lovepicture.nosdn.127.net/4378624425962012850?imageView',
        'https://lovepicture.nosdn.127.net/4320215680473437778?imageView',
        'https://lovepicture.nosdn.127.net/-5265629520073878840?imageView',
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
        $master_pid = intval(Kit::getConfig($this->master_pid_file));
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
        $master_pid = intval(Kit::getConfig($this->master_pid_file));
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
        if(file_exists($this->master_pid_file)) {
            $master_pid = intval(Kit::getConfig($this->master_pid_file));
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
                $msg = "master_pid_file not exist !!!!";
                $this->dangerKill($msg);
            }
        }
    }

    /**
     * 捕获Server运行期致命错误
     * 'https://wiki.swoole.com/wiki/page/305.html
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
        $redis  = new RedisPackage([],0);
        $redis->flushall();

        \swoole_set_process_name($this->process_name);

        //\swoole_server 加反斜杠 表示当前类不在当前的命名空间内
        $this->serv = new \swoole_websocket_server($this->swoole_ip, $this->swoole_port);
        $this->serv->set(array(
            'reactor_num' => 2, //通过此参数来调节poll线程的数量，以充分利用多核
            'daemonize' => true, //加入此参数后，执行php server.php将转入后台作为守护进程运行,ps -ef | grep {this->process_name}
            'worker_num' => 3,//worker_num配置为CPU核数的1-4倍即可
            'dispatch_mode' => 2,//'https://wiki.swoole.com/wiki/page/277.html
            'max_request' => 100,//此参数表示worker进程在处理完n次请求后结束运行，使用Base模式时max_request是无效的
            'backlog' => 128,   //此参数将决定最多同时有多少个待accept的连接，swoole本身accept效率是很高的，基本上不会出现大量排队情况。
            'log_level' => 5,//'https://wiki.swoole.com/wiki/page/538.html
            'log_file' => '/var/www/charRoom/runtime/log_file.'.date("Ym").'.txt',// 'https://wiki.swoole.com/wiki/page/280.html 仅仅是做运行时错误记录，没有长久存储的必要。
            'heartbeat_check_interval' => 30, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
            'heartbeat_idle_time' => 3600, //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过heartbeat_idle_time会把这个连接关闭。
            'task_worker_num' => 2,
            'pid_file'=> $this->master_pid_file,//kill -SIGUSR1 $(cat server.pid)  重启所有worker进程
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

        //'https://wiki.swoole.com/wiki/page/19.html
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

    public static function md5Check($sessid,$md5) {
        $new_md5 = md5($sessid.self::$md5Key);
        return $new_md5 == $md5;
    }

    /**
     * @return mixed
     * 设置唯一标识
     */
    public static function getSessid() {
        $sessid = Cookie::get('sessid');
        $md5    = Cookie::get('md5');
        if(empty($sessid) || empty($md5)) {
            $sessid = uniqid().mt_rand(100000,999999);
            $md5    = md5($sessid.self::$md5Key);
            Cookie::clear();

            Cookie::set('sessid',$sessid, 86400 * 365);
            Cookie::set('md5',$md5, 86400 * 365);
        }

        return $sessid;
    }


    /**
     * @param $serv
     * @param $frame
     * 客户端连接服务器
     */
    public function pokerOpen($serv, $frame) {
        $count = count($serv->connections);

        $serv->push($frame->fd,Kit::json_response(100,'ok',['icon'=>'','fd'=>$frame->fd,'online'=>$count]));
        foreach($serv->connections as $fd) {
            if($fd != $frame->fd) {
                $serv->push($fd,Kit::json_response(20,'ok',[
                    'online'=> $count,
                    'msg'=> '',
                ]));
            }
        }
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

                $dataStr = $frame->data;
                $data    = json_decode($dataStr,true);

                if(empty($data) || !isset($data['md5']) || !isset($data['sessid']) || !isset($data['msg'])) {
                    return $serv->push($frame->fd,Kit::json_response(1,'ok',[
                        'msg'  =>'参数错误'.$frame->data,
                        'icon' =>"http://pics.sc.chinaz.com/Files/pic/icons128/5938/i6.png",
                        'fd'   =>$frame->fd,
                    ]));
                }

                $sessid = $data['sessid'];
                $ukey   = Model_Keys::uinfo($sessid);
                $redis->expire($ukey,600);

                $check = self::md5Check($data['sessid'],$data['md5']);
                if(!$check) {
                    return $serv->push($frame->fd,Kit::json_response(1,'ok',[
                        'msg'  =>'服务器消息：别瞎搞',
                        'icon' =>"http://pics.sc.chinaz.com/Files/pic/icons128/5938/i6.png",
                        'fd'   =>$frame->fd,
                    ]));
                }

                $data['msg'] = trim($data['msg']);
                if($data['msg'] !== "") {
                    $data['msg'] = htmlspecialchars_decode($data['msg']);
                    $data['msg'] = preg_replace("/<(.*?)>/","",$data['msg']);

                    $data['msg'] = mb_strlen($data['msg'],'utf-8') > 100 ? mb_substr($data['msg'],0,100) : $data['msg'];
                    $code  = isset($data['code']) ? $data['code'] : 0;

                    if($code == 10) {
                        //socket链接成功
                        //设置昵称头像

                        if(empty($sessid)) {
                            return $serv->push($frame->fd,Kit::json_response(-1,'请勿禁用cookie'));
                        }

                        $userStr =  $redis->get($ukey);
                        $user    = json_decode($userStr,true);
                        $sessidAndFd = Model_Keys::sessidAndFd();

                        if(empty($user)) {

                            $icon  = empty($data['icon']) ? self::getIconByFd($frame->fd) : "http://chatroom.ivisionsky.com/{$data['icon']}";
                            $nick  = empty($data['nick']) ? Randomname::createName() : $data['nick'];
                            $user  = [
                                'nick' => $nick,
                                'icon' => $icon,
                                'fd'   => $frame->fd,
                            ];

                        } else {
                            $user['fd'] = $frame->fd;
                        }

                        $redis->SETEX($ukey,600,json_encode($user));
                        $redis->hset($sessidAndFd,$frame->fd,$sessid);

                        foreach($serv->connections as $fd) {
                            $serv->push($fd,Kit::json_response($code,'ok',[
                                'msg'=> date("Y-m-d H:i")." <span style='font-weight: bolder;color: #ff0000'>{$user['nick']}</span> 骚年上线",
                            ]));
                        }

                        return  true;

                    } elseif($code == 12) { //有无开发者详细信息
                        $html = " 【拍黄片爱好者】: 77795772 <br/>";
                        $html .= "【82年的老套路】: 1311552030<br/>";
                        $html .= "【Clarence】: 851133067<br/>";
                        $html .= "【php城管】: 961627404";

                        return $serv->push($frame->fd,Kit::json_response(1,'ok',[
                            'msg'  =>'核心组成详细信息：<br />' . $html,
                            'icon' =>"/img/lk.jpg",
                            'fd'   =>$frame->fd,
                        ]));

                    } elseif($code == 13) { //有无开发者详细信息
                        $html = " 由 PHP7 + Mysql + Redis + swoole 实现， 服务器1核1cpu并发能力1.6万左右";
                        return $serv->push($frame->fd,Kit::json_response(1,'ok',[
                            'msg'  => $html,
                            'icon' =>"/img/lk.jpg",
                            'fd'   =>$frame->fd,
                        ]));

                    } elseif($code == 14) { //GitHub源码地址
                        $html = "<a href='https://github.com/lyxlk/CharRoom' target='_blank'>GitHub源码地址 ： https://github.com/lyxlk/CharRoom</a>";
                        return $serv->push($frame->fd,Kit::json_response(1,'ok',[
                            'msg'  => $html,
                            'icon' =>"/img/lk.jpg",
                            'fd'   =>$frame->fd,
                        ]));

                    } elseif($code == 15) { //h5棋牌
                        $html = "<a href='http://www.ivisionsky.com' target='_blank'>H5棋牌 ： http://www.ivisionsky.com</a>";
                        return $serv->push($frame->fd,Kit::json_response(1,'ok',[
                            'msg'  => $html,
                            'icon' =>"/img/lk.jpg",
                            'fd'   =>$frame->fd,
                        ]));

                    } elseif($code == 16) { //join
                            $html = 'come with us ! <br /><img style="width: 45%;height: 45%" src="http://chatroom.ivisionsky.com/img/qun.png">';
                        return $serv->push($frame->fd,Kit::json_response(1,'ok',[
                            'msg'  => $html,
                            'icon' =>"/img/lk.jpg",
                            'fd'   =>$frame->fd,
                        ]));

                    } else {
                        $info = $serv->getClientInfo($frame->fd);
                        $serv->task(json_encode([
                            'msg'=> $data['msg'],
                            'fd'=>$frame->fd,
                            'ip' =>isset($info['remote_ip']) ? $info['remote_ip'] : '',
                        ]));

                        $sessidAndFd = Model_Keys::sessidAndFd();
                        $sessid = $redis->hget($sessidAndFd,$frame->fd);
                        $ukey   = Model_Keys::uinfo($sessid);
                        $userStr =  $redis->get($ukey);
                        $user    = json_decode($userStr,true);
                        if(empty($user)) {
                            return $serv->push($frame->fd,Kit::json_response(-2,'re_load'));
                        } else {
                            $icon    = isset($user['icon']) ? $user['icon'] : "";
                            $nick    = isset($user['nick']) ? $user['nick'] : "";
                            $time    = date("H:i:s");
                            foreach($serv->connections as $fd) {
                                $serv->push($fd,Kit::json_response(1,'ok',[
                                    'msg'=> $data['msg'],
                                    'code' => $code,
                                    'icon'=>$icon,
                                    'nick'=>$nick,
                                    'time'=>$time,
                                    'fd'=>$frame->fd,
                                ]));
                            }
                        }

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
     * @return bool
     * 客户端链接关闭回掉
     */
    public function pokerClose($serv, $fd) {
        $sessidAndFd = Model_Keys::sessidAndFd();
        $redis       = new RedisPackage([],$serv->worker_id);

        $count = count($serv->connections) - 1;

        $sessid  = $redis->hget($sessidAndFd,$fd);
        $ukey    = Model_Keys::uinfo($sessid);
        $userStr = $redis->get($ukey);
        $user    = json_decode($userStr,true);
        if(!empty($user)) {
            foreach($serv->connections as $_fd) {
                if($fd != $_fd) {
                    $serv->push($_fd,Kit::json_response(20,'ok',[
                        'online'=> $count,
                        'msg'   => date("Y-m-d H:i")." <span style='font-weight: bolder;color: #008bff'>{$user['nick']}</span> 骚年下线了",
                    ]));
                }
            }
        }
        $redis->hdel($sessidAndFd,$fd);

        return true;
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
