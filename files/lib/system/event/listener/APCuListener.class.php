<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\exception\SystemException;
use wcf\system\Regex;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2012 Jan Altensen (Stricted)
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     de.stricted.wcf.apc
 * @subpackage  system.event.listener
 * @category    Community Framework
 */
class APCuListener implements IEventListener {
	/**
	 * @see \wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj->cacheData['source'] == 'wcf\system\cache\source\ApcuCacheSource') {
			// set version
			$eventObj->cacheData['version'] = phpversion('apcu');
			
			$apcuinfo = apcu_cache_info('user');
			$cacheList = $apcuinfo['cache_list'];
			usort($cacheList, function ($a, $b) {
				return $a['key'] > $b['key'];
			});
			
			$prefix = new Regex('^WCF_'.substr(sha1(WCF_DIR), 0, 10) . '_');
			foreach ($cacheList as $cache) {
				if (!$prefix->match($cache['key'])) continue;
				
				// get additional cache information
				$eventObj->caches['data']['apcu'][] = array(
					'filename' => $prefix->replace($cache['key'], ''),
					'filesize' => $cache['mem_size'],
					'mtime' => $cache['mtime']
				);
				
				$eventObj->cacheData['files']++;
				$eventObj->cacheData['size'] += $cache['mem_size'];
			}
		}
	}
}
?>