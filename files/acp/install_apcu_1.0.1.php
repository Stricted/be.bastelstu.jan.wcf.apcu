<?php
use wcf\system\cache\CacheHandler;

/**
 * @author		Jan Altensen (Stricted)
 * @copyright	2013 Jan Altensen (Stricted)
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		be.bastelstu.jan.wcf.apcu
 * @category	Community Framework
 */
// clear cache
CacheHandler::getInstance()->flushAll();
