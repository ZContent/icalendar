<?php

/**
 * @package      Zap Calendar Library Framework
 *
 * @copyright   Copyright (C) 2006 - 2016 by Dan Cogliano
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 *
 * For more information, visit https://github.com/zcontent/icalendar
 */

// No direct access
defined('_ZAPCAL') or die( 'Restricted access' );

// set MAXYEAR to 2036 for 32 bit systems, can be higher for 64 bit systems
define('_ZAPCAL_MAXYEAR', 2036);

// set MAXREVENTS to maximum #of repeating events
define('_ZAPCAL_MAXREVENTS', 5000);

require_once(_ZAPCAL_BASE . '/includes/date.php');
require_once(_ZAPCAL_BASE . '/includes/recurringdate.php');
require_once(_ZAPCAL_BASE . '/includes/ical.php');
require_once(_ZAPCAL_BASE . '/includes/timezone.php');
