<?php
namespace Swoole;

/**
 * Swoole库加载器
 * @author Tianfeng.Han
 * @package SwooleSystem
 * @subpackage base
 *
 */
class Loader
{
	/**
	 * 命名空间的路径
	 */
	protected static $namespaces;
	static $swoole;
    static $_objects;

    function __construct($swoole)
    {
        self::$swoole = $swoole;
        self::$_objects = array(
            'model'  => new \ArrayObject,
            'object' => new \ArrayObject
        );
    }

    /**
     * for composer
     */
    static function vendorInit()
    {
        require __DIR__ . '/../lib_config.php';
    }

	/**
	 * 加载一个模型对象
	 * @param $model_name string 模型名称
	 * @return $model_object 模型对象
	 */
    static function loadModel($model_name)
    {
        if (isset(self::$_objects['model'][$model_name]))
        {
            return self::$_objects['model'][$model_name];
        }
        else
        {
            $model_file = \Swoole::$app_path . '/models/' . $model_name . '.model.php';
            if (!file_exists($model_file))
            {
                Error::info('MVC错误', "不存在的模型, <b>$model_name</b>");
            }
            require($model_file);
            self::$_objects['model'][$model_name] = new $model_name(self::$swoole);
            return self::$_objects['model'][$model_name];
        }
    }

	/**
	 * 自动载入类
	 * @param $class
	 */
	static function autoload($class)
	{
		$root = explode('\\', trim($class, '\\'), 2);
		if (count($root) > 1 and isset(self::$namespaces[$root[0]]))
		{
            include self::$namespaces[$root[0]].'/'.str_replace('\\', '/', $root[1]).'.php';
		}
	}

	/**
	 * 设置根命名空间
	 * @param $root
	 * @param $path
	 */
	static function addNameSpace($root, $path)
	{
		self::$namespaces[$root] = $path;
	}
}