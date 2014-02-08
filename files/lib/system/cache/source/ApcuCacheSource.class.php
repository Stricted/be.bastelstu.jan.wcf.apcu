<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\util\APC;
use wcf\util\StringUtil;

/**
 * ApcuCacheSource is an implementation of CacheSource that uses APC(u) to store cached variables.
 *
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     be.bastelstu.jan.wcf.apcu
 * @category    Community Framework
 */
class ApcuCacheSource implements ICacheSource {
	/**
	 * apc object
	 * @var \wcf\util\APC
	 */
	protected $apc = null;
	
	/**
	 * key prefix
	 * @var	string
	 */
	protected $prefix = '';
	
	/**
	 * Creates a new ApcCacheSource object.
	 */
	public function __construct() {
		$this->apc = APC::getInstance();
		
		// set variable prefix to prevent collision
		$this->prefix = 'WCF_'.substr(sha1(WCF_DIR), 0, 10) . '_';
	}
	
	/**
	 * Flushes a specific cache, optionally removing caches which share the same name.
	 * 
	 * @param	string		$cacheName
	 * @param	boolean		$useWildcard
	 */
	public function flush($cacheName, $useWildcard) {
		if ($useWildcard) {
			$this->removeKeys($this->prefix . $cacheName . '(\-[a-f0-9]+)?');
		}
		else {
			$this->apc->delete($this->prefix . $cacheName);
		}
	}
	
	/**
	 * Clears the cache completely.
	 */
	public function flushAll() {
		$this->removeKeys();
	}
	
	/**
	 * Returns a cached variable.
	 * 
	 * @param	string		$cacheName
	 * @param	integer		$maxLifetime
	 * @return	mixed
	 */
	public function get($cacheName, $maxLifetime) {
		return $this->apc->fetch($this->prefix . $cacheName);
	}
	
	/**
	 * Stores a variable in the cache.
	 * 
	 * @param	string		$cacheName
	 * @param	mixed		$value
	 * @param	integer		$maxLifetime
	 */
	public function set($cacheName, $value, $maxLifetime) {
		$this->apc->store($this->prefix . $cacheName, $value, $this->getTTL($maxLifetime));
	}
	
	/**
	 * Returns time to live in seconds, defaults to 3 days.
	 * 
	 * @param	integer		$maxLifetime
	 * @return	integer
	 */
	protected function getTTL($maxLifetime = 0) {
		if ($maxLifetime && ($maxLifetime <= (60 * 60 * 24 * 30) || $maxLifetime >= TIME_NOW)) {
			return $maxLifetime;
		}
		
		// default TTL: 3 days
		return (60 * 60 * 24 * 3);
	}
	
	/**
	 * remove cache data
	 *
	 * @param	string	$pattern	<optional>
	 */
	public function removeKeys($pattern = null) {
		$regex = null;
		if ($pattern !== null) $regex = new Regex('^'.$pattern.'$');
		
		$apcCacheInfo = $this->apc->cache_info();
		foreach ($apcCacheInfo as $cache) {
			if ($regex === null) {
				if (StringUtil::startsWith($cache['info'], $this->prefix)) {
					$this->apc->delete($cache['info']);
				}
			}
			else if ($regex->match($cache['info'])) {
				$this->apc->delete($cache['info']);
			}
		}
	}
}
