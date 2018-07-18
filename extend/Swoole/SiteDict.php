<?php
namespace Swoole;

/**
 * 字典处理器，依赖于缓存系统
 * 读取/写入来自于文件系统的一个数据，并写入缓存
 * @author Tianfeng.Han
 * @package SwooleSystem
 * @subpackage dict
 */
class SiteDict
{
	static $cache_life = 600;
	static $data_dir = DICTPATH;
	public $table = 'site_dict';

	function __construct()
	{
		#import('app.SwooleDict');
	}
	/**
	 * 读取字典内容
	 * @param $dictname
	 */
	static function get($dictname)
	{
		if(!\Swoole::$php->cache) Error::info('SiteDict Cache Error','Please load Cache!');
		$cache_key = 'sitedict_'.$dictname;
		$$dictname = \Swoole::$php->cache->get($cache_key);
		if(empty($$dictname))
		{
			$data_file = self::$data_dir.'/'.$dictname.'.php';
			if(!file_exists($data_file)) Error::info('SiteDict dict file not found!',"File <b>$data_file</b> not found!");
			require($data_file);
			\Swoole::$php->cache->set($cache_key,$$dictname,self::$cache_life);
		}
		return $$dictname;
	}
	/**
	 * 写入字典内容
	 * @param $dictname
	 */
	static function set($dictname,$dict)
	{
		if(!\Swoole::$php->cache) Error::info('SiteDict Cache Error','Please load Cache!');
		$filename = self::$data_dir.'/'.$dictname.'.php';
		self::write($dictname,$dict,$filename);
		self::delete($dictname);
	}
	static function write($dictname,&$dict,$filename)
	{
	    file_put_contents($filename,"<?php\n\${$dictname}=".var_export($dict,true).';');
	}
	/**
	 * 删除字典内容
	 * @param $dictname
	 */
	static function delete($dictname)
	{
		$cache_key = 'sitedict_'.$dictname;
		\Swoole::$php->cache->delete($cache_key);
	}
}
