本项目是由 [农码一生] Q群【 147271488 】

推出的基于PHP7+Swoole+Redis+Mysql实现的实时聊天系统

PHP框架是：ThinkPHP5.0 (其实啥框架都行，只要你喜欢，可以随意瞎搞)

2.0.0版本已上线 https://icu.ivisionsky.com

-------------旧版本分割线-----------------

项目演示地址 : http://chatroom.ivisionsky.com  

GoLang重构的项目演示地址在 : http://go.ivisionsky.com

GoLang重构的H5棋牌地址在 : https://www.ivisionsky.com PC端体验请按F12,点击Toggle Device ToolBar设置为手机模式体验最佳

欢迎各位同仁一起推进、做出一个有意义的项目

CharRoom单词写错了，有洁癖的自行修改

要觉得项目对您有帮助就点个赞吧~~~

服务器启动/关闭
===============
 + 一律需要将项目“charRoom”放置在 /var/www/ 下，没有就自己创建；注意项目名的大小写！！！
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
