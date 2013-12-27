<?php
namespace wcf\system\event\listener;
use wcf\system\cache\CacheHandler;
use wcf\data\option\OptionEditor;
use wcf\system\event\IEventListener;
use wcf\system\WCF;

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
