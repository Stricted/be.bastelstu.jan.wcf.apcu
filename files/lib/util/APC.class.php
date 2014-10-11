<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     be.bastelstu.jan.wcf.apcu
 * @category    Community Framework
 */
class APC extends SingletonFactory {
	/**
	 * php extension
	 * @var	string
	 */
	protected $apcu = false;
	
	/**
	 * APC(u) version
	 * @var integer
	 */
	public $version = 0;
	
	/**
	 * init APC class
	 */
	protected function init () {
		if (extension_loaded("apcu")) {
			$this->apcu = true;
			$this->version = phpversion('apcu');
		} else if (extension_loaded("apc")) {
			$this->version = phpversion('apc');
		} else {
			throw new SystemException('APC/APCu support is not enabled.');
		}
	}
	
	/**
	 * deletes a cache item
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	public function delete ($key) {
		if ($this->exists($key)) {
			if ($this->apcu) {
				return apcu_delete($key);
			}
			else {
				return apc_delete($key);
			}
		}
	}
	
	/**
	 * fetch a cache item
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public function fetch ($key) {
		if ($this->exists($key)) {
			$cacheTime = $this->getCacheTime($key);
			if ($cacheTime['ttl'] > 0 && (TIME_NOW - $cacheTime['mtime']) > $cacheTime['ttl']) {
				$this->delete($key);
				return null;
			}
			
			if ($this->apcu) {
				return apcu_fetch($key);
			}
			else {
				return apc_fetch($key);
			}
		}
		
		return null;
	}
	
	/**
	 * store a cache item
	 *
	 * @param	string	$key
	 * @param	string	$var
	 * @param	integer	$ttl <optional>
	 * @return	boolean
	 */
	public function store ($key, $var, $ttl = 0) {
		$this->delete($key); // remove cache entry if allready exists
		
		if ($this->apcu) {
			apcu_store($key, $var, $ttl);
		}
		else {
			apc_store($key, $var, $ttl);
		}
	}
	
	/**
	 * Checks if APC/APCu key exists
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	protected function exists ($key) {
		$cacheItems = array();
		foreach ($this->cache_info() as $item) {
			$cacheItems[] = $item['info'];
		}
		return in_array($key, $cacheItems);
	}
	
	/**
	 * get cache lifetime
	 *
	 * @param	string	$key
	 * @return	array
	 */
	protected function getCacheTime ($key) {
		$cacheItems = array();
		foreach ($this->cache_info() as $item) {
			if ($item['info'] == $key) {
				return array(
					"ttl" => $item['ttl'],
					"mtime" => $item['mtime']
					);
			}
		}
	}
		
	/**
	 * get cache items
	 *
	 * @param	string	$cache_type <optional>
	 * @return	array
	 */
	public function cache_info ($cache_type = "") {
		$info = array();
		
		if ($this->apcu) {
			$apcinfo = apcu_cache_info($cache_type);
		}
		else {
			// APC need cache_type = 'user'
			if ($cache_type == "") $cache_type = "user";
			
			$apcinfo = apc_cache_info($cache_type);
		}
		
		if (isset($apcinfo['cache_list'])) {
			$cacheList = $apcinfo['cache_list'];
			
			usort($cacheList, array($this, "usort"));
			
			foreach ($cacheList as $cache) {
				// make APCu output compatible with APC
				
				if (isset($cache['key'])) {
					$cache['info'] = $cache['key'];
					unset($cache['key']);
				}
				
				if (!isset($cache['type'])) {
					$cache['type'] = 'user';
				}
				
				if (isset($cache['nhits'])) {
					$cache['num_hits'] = $cache['nhits'];
					unset($cache['nhits']);
				}
				
				if (isset($cache['ctime'])) {
					$cache['creation_time'] = $cache['ctime'];
					unset($cache['ctime']);
				}
				
				if (isset($cache['dtime'])) {
					$cache['deletion_time'] = $cache['dtime'];
					unset($cache['dtime']);
				}
				
				if (isset($cache['atime'])) {
					$cache['access_time'] = $cache['atime'];
					unset($cache['atime']);
				}
				
				if (isset($cache['modification_time'])) {
					$cache['mtime'] = $cache['modification_time'];
					unset($cache['modification_time']);
				}
				
				$info[] = $cache;
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
	protected function usort ($a, $b) {
		if (isset($a['key']) && isset($b['key'])) {
			return $a['key'] > $b['key'];
		}
		else if (isset($a['info']) && isset($b['info'])) {
			return $a['info'] > $b['info'];
		}
	}
}
