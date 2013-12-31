<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\APC;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     be.bastelstu.jan.wcf.apcu
 * @category    Community Framework
 */
class APCuListener implements IEventListener {
	/**
	 * @see \wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		switch ($className) {
			case "wcf\acp\page\CacheListPage":
				if ($eventObj->cacheData['source'] == 'wcf\system\cache\source\ApcuCacheSource') {
					APC::init();
					
					// set version
					$eventObj->cacheData['version'] = APC::$version;
					
					$cacheList = APC::cache_info('user');
					
					$prefix = new Regex('^WCF_'.substr(sha1(WCF_DIR), 0, 10) . '_');
					$data = array();
					$eventObj->cacheData['apcfiles'] = 0;
					$eventObj->cacheData['apcusize'] = 0;
					foreach ($cacheList as $cache) {
						if (!$prefix->match($cache['info'])) continue;
						
						// get additional cache information
						$data['data']['apcu'][] = array(
							'filename' => $prefix->replace($cache['info'], ''),
							'filesize' => $cache['mem_size'],
							'mtime' => $cache['mtime']
						);
						$eventObj->cacheData['apcfiles']++;
						$eventObj->cacheData['apcusize'] += $cache['mem_size'];
					}
					$eventObj->caches = array_merge($data, $eventObj->caches);
				}
				break;
			
			case "wcf\system\option\OptionHandler":
				$eventObj->cachedOptions['cache_source_type']->modifySelectOptions($eventObj->cachedOptions['cache_source_type']->selectOptions . "\napcu:wcf.acp.option.cache_source_type.apcu");
				
				/* dirty but i wait for pull request https://github.com/WoltLab/WCF/pull/1630 */
				$eventObj->cachedOptions['cache_source_type']->enableOptions = $eventObj->cachedOptions['cache_source_type']->enableOptions . "\napcu:!cache_source_memcached_host";
				break;
			
			case "wcf\acp\action\UninstallPackageAction":
				if (isset($_POST['packageID']) && !empty($_POST['packageID'])) {
					$packageID = intval($_POST['packageID']);
				} else {
					$packageID = 0;
				}
				if ($packageID) {
					$sql = "SELECT * FROM wcf".WCF_N."_package where package = ? LIMIT 1";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array("be.bastelstu.jan.wcf.apcu"));
					$row = $statement->fetchArray();
					if ($packageID == $row['packageID']) {
						// set cache to disk if apc(u) is enabled
						$sql = "UPDATE	wcf".WCF_N."_option
							SET	optionValue = ?
							WHERE	optionName = ?
								AND optionValue = ?";
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute(array(
							'disk',
							'cache_source_type',
							'apcu'
						));
					}
				}
				break;
		}
	}
}
