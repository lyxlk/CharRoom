-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2018-07-26 02:07:49
-- 服务器版本： 5.6.39
-- PHP Version: 7.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chatroom`
--

-- --------------------------------------------------------

--
-- 表的结构 `t_modify_uinfo`
--

CREATE TABLE `t_modify_uinfo` (
  `id` bigint(11) UNSIGNED NOT NULL COMMENT 'ID',
  `fd` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `img` varchar(200) NOT NULL DEFAULT '',
  `nick` varchar(200) NOT NULL DEFAULT '',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `add_time` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `t_msg`
--

CREATE TABLE `t_msg` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID',
  `fd` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `msg` varchar(255) NOT NULL DEFAULT '' COMMENT '聊天内容',
  `add_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '行为（0：登陆，1发消息）',
  `IP` varchar(20) NOT NULL DEFAULT '' COMMENT 'IP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='聊天表';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_modify_uinfo`
--
ALTER TABLE `t_modify_uinfo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_msg`
--
ALTER TABLE `t_msg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `add_time` (`add_time`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `t_modify_uinfo`
--
ALTER TABLE `t_modify_uinfo`
  MODIFY `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=44;

--
-- 使用表AUTO_INCREMENT `t_msg`
--
ALTER TABLE `t_msg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=47080;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
