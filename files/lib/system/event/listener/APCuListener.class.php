<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\util\APCUtil;

/**
 * @author		Jan Altensen (Stricted)
 * @copyright	2013 Jan Altensen (Stricted)
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		be.bastelstu.jan.wcf.apcu
 * @category	Community Framework
 */
class APCuListener implements IEventListener {
	/**
	 * @see \wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj->cacheData['source'] == 'wcf\system\cache\source\ApcuCacheSource') {
			$apc = new APCUtil();
			// set version
			$eventObj->cacheData['version'] = $apc->version;
			
			$cacheList = $apc->cache_info('user');
			
			$prefix = new Regex('^WCF_'.substr(sha1(WCF_DIR), 0, 10) . '_');
			$data = array();
			foreach ($cacheList as $cache) {
				if (!$prefix->match($cache['info'])) continue;
				
				// get additional cache information
				$data['data']['apcu'][] = array(
					'filename' => $prefix->replace($cache['info'], ''),
					'filesize' => $cache['mem_size'],
					'mtime' => $cache['mtime']
				);
				$eventObj->cacheData['files']++;
				$eventObj->cacheData['size'] += $cache['mem_size'];
			}
			$eventObj->caches = array_merge($data, $eventObj->caches);
		}
	}
}
?>