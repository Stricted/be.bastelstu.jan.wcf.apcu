<?php
namespace wcf\system\event\listener;
use wcf\system\cache\CacheHandler;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\APC;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     be.bastelstu.jan.wcf.apcu
 * @category    Community Framework
 */
class APCuListener implements IParameterizedEventListener {
	/**
	 * @see \wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		switch ($className) {
			case "wcf\acp\page\IndexPage":
				$eventObj->server['cache'] = get_class(CacheHandler::getInstance()->getCacheSource());
				break;
			case "wcf\acp\page\CacheListPage":
				if ($eventObj->cacheData['source'] == 'wcf\system\cache\source\ApcuCacheSource') {
					$apc = APC::getInstance();
					
					// set version
					$eventObj->cacheData['version'] = $apc->version;
					
					$prefix = new Regex('^'.WCF_UUID.'_');
					$data = array();
					foreach ($apc->cache_info() as $cache) {
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
				break;
			
			case "wcf\system\option\OptionHandler":
				$eventObj->cachedOptions['cache_source_type']->modifySelectOptions($eventObj->cachedOptions['cache_source_type']->selectOptions . "\napcu:wcf.acp.option.cache_source_type.apcu");
				$eventObj->cachedOptions['cache_source_type']->modifyEnableOptions($eventObj->cachedOptions['cache_source_type']->enableOptions . "\napcu:!cache_source_memcached_host");
				break;
			
			case "wcf\acp\action\UninstallPackageAction":
				$packageID = 0;
				if (isset($_POST['packageID']) && !empty($_POST['packageID'])) $packageID = intval($_POST['packageID']);
				
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
