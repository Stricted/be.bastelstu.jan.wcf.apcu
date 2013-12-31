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
	 * init APC class
	 */
	public static function init () {
		if (extension_loaded("apcu")) {
			self::$extension = "apcu";
			self::$version = phpversion('apcu');
		} else if (extension_loaded("apc")) {
			self::$extension = "apc";
			self::$version = phpversion('apc');
		} else {
			throw new SystemException('APC support is not enabled.');
		}
		
		if (self::$extension == "apcu" && version_compare(self::$version, '4.0.1', '<')) {
			throw new SystemException('APCu 4.0.1 and 4.0.0 is not supported.');
		}
	}
	
	/**
	 * deletes a cache item
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	public static function delete ($key) {
		$delete = self::$extension."_delete";
		return $delete($key);
	}
	
	/**
	 * fetch a cache item
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public static function fetch ($key) {
		$fetch = self::$extension."_fetch";
		return $fetch($key);
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
		$store = self::$extension."_store";
		return $store($key, $var, $ttl);
	}
	
	/**
	 * clear cache
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	public static function clear_cache ($key = "user") {
		$clear_cache = self::$extension."_clear_cache";
		return $clear_cache($key);
	}
	
	
	/**
	 * get cache items
	 *
	 * @param	string	$key
	 * @return	array
	 */
	public static function cache_info ($key = "user") {
		$info = array();
		$cache_info = self::$extension."_cache_info";
		$apcinfo = $cache_info($key);
		
		if (isset($apcinfo['cache_list'])) {
			$cacheList = $apcinfo['cache_list'];
			
			usort($cacheList, array("self", "usort"));
			
			foreach ($cacheList as $cache) {
				if (self::$extension == "apcu" && version_compare(self::$version, '4.0.3', '<')) {
					$apcu = $cache;
					$apcu['info'] = $cache['key'];
					$info[] = $apcu;
				} else {
					$info[] = $cache;
				}
			}
		}
		
		return $info;
	}
	
	/**
	 * sort the given data
	 *
	 * @param	array	$a
	 * @param	array	$b
	 * @return	array
	 */
	protected static function usort ($a, $b) {
		if (self::$extension == "apcu") {
			return $a['key'] > $b['key'];
		} else {
			return $a['info'] > $b['info'];
		}
	}
}
