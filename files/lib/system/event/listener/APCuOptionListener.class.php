<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;

/**
 * @author		Jan Altensen (Stricted)
 * @copyright	2013 Jan Altensen (Stricted)
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		be.bastelstu.jan.wcf.apcu
 * @category	Community Framework
 */
class APCuOptionListener implements IEventListener {
	/**
	 * @see \wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$selectOptions = $eventObj->cachedOptions['cache_source_type']->selectOptions . "\napcu:wcf.acp.option.cache_source_type.apcu";
		$eventObj->cachedOptions['cache_source_type']->modifySelectOptions($selectOptions);
	}
}
