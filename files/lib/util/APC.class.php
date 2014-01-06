<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     be.bastelstu.jan.wcf.apcu
 * @category    Community Framework
 */
class APC {
	/**
	 * php extension
	 * @var	string
	 */
	protected static $apcu = false;
	
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
			self::$apcu = true;
			self::$version = phpversion('apcu');
		} else if (extension_loaded("apc")) {
			self::$version = phpversion('apc');
		} else {
			throw new SystemException('APC/APCu support is not enabled.');
		}
	}
	
	/**
	 * deletes a cache item
	 *
	 * @param	string	$key
	 */
	public static function delete ($key) {
		if (self::$apcu) apcu_delete($key);
		else apc_delete($key);
	}
	
	/**
	 * fetch a cache item
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public static function fetch ($key) {
		if (self::$apcu) return apcu_fetch($key);
		else return apc_fetch($key);
	}
	
	/**
	 * store a cache item
	 *
	 * @param	string	$key
	 * @param	string	$var
	 * @param	integer	$ttl <optional>
	 * @return	boolean
	 */
	public static function store ($key, $var, $ttl = 0) {
		if (self::$apcu) apcu_store($key, $var, $ttl);
		else apc_store($key, $var, $ttl);
	}
	
	/**
	 * get cache items
	 *
	 * @param	string	$key
	 * @param	string	$cache_type <optional>
	 * @return	array
	 */
	public static function cache_info ($cache_type = "") {
		$info = array();
		if (self::$apcu) $apcinfo = apcu_cache_info($cache_type);
		else $apcinfo = apc_cache_info($cache_type);
		
		if (isset($apcinfo['cache_list'])) {
			$cacheList = $apcinfo['cache_list'];
			
			usort($cacheList, array("self", "usort"));
			
			foreach ($cacheList as $cache) {
				if (isset($cache['key'])) {
					$cache['info'] = $cache['key'];
					unset($cache['key']);
				}
				
				if (isset($cache['info'])) {
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
		if (isset($a['key']) && isset($b['key'])) {
			return $a['key'] > $b['key'];
		}
		else if (isset($a['info']) && isset($b['info'])) {
			return $a['info'] > $b['info'];
		}
	}
}
