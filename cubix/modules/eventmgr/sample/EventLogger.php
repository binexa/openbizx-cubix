<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.eventmgr.sample
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id$
 */

use Openbiz\Openbiz;

class EventLogger
{
	public function observe($event)
	{
  		$triggerObj = $event->getTarget();
		$triggerEvent = $event->getName();
		$params = $event->getParams();
		// get eventlog service
		$eventLog = Openbiz::getService(OPENBIZ_EVENTLOG_SERVICE);
		// log message
		$eventLog->Log($triggerEvent,"triggered by ".$triggerObj->objectName);
	}
}

