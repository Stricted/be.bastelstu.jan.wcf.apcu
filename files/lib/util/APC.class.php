<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     be.bastelstu.jan.wcf.apcu
 * @category    Community Framework
 */
class APC {
	/**
	 * php extension
	 * @var	string
	 */
	protected static $extension = "";
	
	/**
	 * APC(u) version
	 * @var integer
	 */
	public static $version = 0;
	
	/**
	 * Creates a new APC object.
	 */
	public static function construct () {
		if (extension_loaded("apcu")) {
			self::$extension = "apcu";
			self::$version = phpversion('apcu');
		} else if (extension_loaded("apc")) {
			self::$extension = "apc";
			self::$version = phpversion('apc');
		} else
			throw new SystemException('APC support is not enabled.');
	}
	
	/**
	 * deletes a cache item
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	public static function delete ($key) {
		if (self::$extension == "apcu")
			return apcu_delete($key);

		return apc_delete($key);
	}
	
	/**
	 * fetch a cache item
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public static function fetch ($key) {
		if (self::$extension == "apcu")
			return apcu_fetch($key);
		
		return apc_fetch($key);
	}
	
	/**
	 * store a cache item
	 *
	 * @param	string	$key
	 * @param	string	$var
	 * @param	integer	$ttl
	 * @return	boolean
	 */
	public static function store ($key, $var, $ttl) {
		if (self::$extension == "apcu")
			return apcu_store($key, $var, $ttl);
		
		return apc_store($key, $var, $ttl);
	}
	
	/**
	 * clear cache
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	public static function clear_cache ($key = "user") {
		if (self::$extension == "apcu")
			return apcu_clear_cache($key);
		
		return apc_clear_cache($key);
	}
	
	
	/**
	 * get cache items
	 *
	 * @param	string	$key
	 * @return	array
	 */
	public static function cache_info ($key = "user") {
		$info = array();
		if (self::$extension == "apcu" && version_compare(self::$version, '4.0.3', '<')) {
			$apcinfo = apcu_cache_info($key);
			if (isset($apcinfo['cache_list'])) {
				$cacheList = $apcinfo['cache_list'];
				
				usort($cacheList, function ($a, $b) {
					return $a['key'] > $b['key'];
				});
				
				foreach ($cacheList as $cache) {
					$apcu = $cache;
					$apcu['info'] = $cache['key'];
					$info[] = $apcu;
				}
			}
		}
		else {
			$cache_info = self::$extension."_cache_info";
			$apcinfo = $cache_info($key);
			if (isset($apcinfo['cache_list'])) {
				$cacheList = $apcinfo['cache_list'];
				
				usort($cacheList, function ($a, $b) {
					return $a['info'] > $b['info'];
				});
				
				foreach ($cacheList as $cache) {
					$info[] = $cache;
				}
			}
		}
		
		return $info;
	}
}
