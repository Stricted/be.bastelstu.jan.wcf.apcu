<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\WCF;
use wcf\data\option\OptionEditor;
use wcf\system\cache\CacheHandler;

/**
 * @author		Jan Altensen (Stricted)
 * @copyright	2013 Jan Altensen (Stricted)
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		be.bastelstu.jan.wcf.apcu
 * @category	Community Framework
 */
class APCuUninstallListener implements IEventListener {
	/**
	 * @see \wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if(isset($_POST['packageID']) && !empty($_POST['packageID']))
			$packageID = intval($_POST['packageID']);
		else
			$packageID = 0;
		
		$sql = "SELECT * FROM wcf".WCF_N."_package where package = :package LIMIT 1";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(":package" => "be.bastelstu.jan.wcf.apcu"));
		$row = $statement->fetchArray();
		if ($packageID && $packageID == $row['packageID']) {
			// restore default cachesource options
			$sql = "UPDATE wcf".WCF_N."_option SET 
	selectOptions = 'disk:wcf.acp.option.cache_source_type.disk
memcached:wcf.acp.option.cache_source_type.memcached
no:wcf.acp.option.cache_source_type.no',
	enableOptions = 'disk:!cache_source_memcached_host
memcached:cache_source_memcached_host
no:!cache_source_memcached_host' 
	WHERE optionName = 'cache_source_type';";
			
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
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
			
			// clear cache
			CacheHandler::getInstance()->flushAll();
			
			// rebuild options.inc.php
			OptionEditor::resetCache();
			OptionEditor::rebuild();
		}
	}
}
