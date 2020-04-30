<?php 
define('S_ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR);

/**
 * class auto load
 * PSR-4规范
 */
final class AutoLoader
{
	private function __construct(){}

	private function __clone(){}

	# 命名空间与路径
	protected static $vndorMap = [
		'Chenmu' => __DIR__ . DIRECTORY_SEPARATOR
	];

	/**
	 * 自动加载类
	 * @param  string $class 调用的类
	 * @return bool
	 */
	public static function autoload(string $class): bool
	{
		$top = substr($class, 0, strpos($class, "\\"));
		$topDir = self::$vndorMap[$top];
		$path = substr($class, strlen($top)) . '.php';
		$file = strtr($topDir . $path, '\\', DIRECTORY_SEPARATOR);
		if (file_exists($file) && is_readable($file)) {
			require_once $file;
			return true;
		}
		return false;
	}
}
spl_autoload_register('AutoLoader::autoload');