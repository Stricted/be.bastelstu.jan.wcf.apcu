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
	 * key prefix
	 * @var	string
	 */
	protected $prefix = '';
	
	/**
	 * Creates a new ApcCacheSource object.
	 */
	public function __construct() {
		APC::init();
		
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
			APC::delete($this->prefix . $cacheName);
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
		if (($data = APC::fetch($this->prefix . $cacheName)) === false) {
			return null;
		}
		
		return $data;
	}
	
	/**
	 * Stores a variable in the cache.
	 * 
	 * @param	string		$cacheName
	 * @param	mixed		$value
	 * @param	integer		$maxLifetime
	 */
	public function set($cacheName, $value, $maxLifetime) {
		APC::store($this->prefix . $cacheName, $value, $this->getTTL($maxLifetime));
	}
	
	/**
	 * Returns time to live in seconds, defaults to 3 days.
	 * 
	 * @param	integer		$maxLifetime
	 * @return	integer
	 */
	protected function getTTL($maxLifetime = 0) {
		if ($maxLifetime) {
			// max lifetime is a timestamp, discard (similar to http://www.php.net/manual/en/memcached.expiration.php)
			if ($maxLifetime > (60 * 60 * 24 * 30)) {
				$maxLifetime = 0;
			}
		}
		
		if ($maxLifetime) {
			return $maxLifetime;
		}
		
		// default TTL: 3 days
		return (60 * 60 * 24 * 3);
	}
	
	/**
	 * @see  \wcf\system\cache\source\ICacheSource::clear()
	 */
	public function removeKeys($pattern = null) {
		$regex = null;
		if ($pattern !== null) {
			$regex = new Regex('^'.$pattern.'$');
		}
		
		$apcCacheInfo = APC::cache_info();
		foreach ($apcCacheInfo as $cache) {
			if ($regex === null) {
				if (StringUtil::startsWith($cache['info'], $this->prefix)) {
					APC::delete($cache['info']);
				}
			}
			else if ($regex->match($cache['info'])) {
				APC::delete($cache['info']);
			}
		}
	}
}
