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
 * @copyright   2013 Jan Altensen (Stricted)
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
	 * @see	\wcf\system\cache\source\ICacheSource::flush()
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
	 * @see	\wcf\system\cache\source\ICacheSource::flushAll()
	 */
	public function flushAll() {
		APC::clear_cache();
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::get()
	 */
	public function get($cacheName, $maxLifetime) {
		if (($data = APC::fetch($this->prefix . $cacheName)) === false) {
			return null;
		}
		
		return $data;
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::set()
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
	 * @see	\wcf\system\cache\source\ICacheSource::clear()
	 */
	public function removeKeys($pattern) {
		$regex = new Regex('^'.$pattern.'$');
		
		$apcuCacheInfo = APC::cache_info('user');
		foreach ($apcuCacheInfo as $cache) {
			if ($regex->match($cache['info'])) {
				$this->apc->delete($cache['info']);
			}
		}
	}
}
