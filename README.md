本项目是由 PHP研究院：Q群【 200979897 】

推出的基于PHP7+Swoole+Redis+Mysql实现的实时聊天系统

PHP框架是：ThinkPHP5.0 (其实啥框架都行，只要你喜欢，可以随意瞎搞)

项目演示地址 : http://chatroom.ivisionsky.com

欢迎各位同仁一起推进、做出一个有意义的项目

CharRoom单词写错了，有洁癖的自行修改

要觉得项目对您有帮助就点个赞吧~~~

服务器启动/关闭
===============
 + cd /var/www/charRoom  && php think Swoole -m "start"
 + cd /var/www/charRoom  && php think Swoole -m "stop"
 
服务器监控相关脚本如下
===============

#### 每天凌晨 4点 重启各种服务器
 + 5  4 * * * service php-fpm restart  >/dev/null 2>&1 &

 + 10 4 * * * service nginx restart  >/dev/null 2>&1 &

 + 15 4 * * * service mysql restart  >/dev/null 2>&1 &

 + 20 4 * * * service redis restart  >/dev/null 2>&1 &
 
#### 防止redis 超负荷运行 挂掉了
 + 18 4 * * * redis-server  /etc/redis.conf  >/dev/null 2>&1 &

 + 19 4 * * * redis-server  /etc/redis6380.conf  >/dev/null 2>&1 &

#### 每5分钟
 + */5 * * * * cd /var/www/charRoom  && php think Swoole -m "monitor"  >/dev/null 2>&1 &

#### 每小时执行一次 重启一下worker
 + 1 * * * *  cd /var/www/charRoom  && php think Swoole -m "reload"  >/dev/null 2>&1 &
