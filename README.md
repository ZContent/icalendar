# iCalendar

The Zap Calendar Library is a PHP library for supporting the iCalendar (RFC 5545) standard.

This library supports reading and writing iCalendar formatted feeds and 
files using PHP.
All iCalendar data is stored in a PHP object tree.
This allows any property to be added to the iCalendar feed without
requiring specialized library function calls.
With power comes responsibility.  Missing or invalid properties can cause 
the resulting iCalendar file to be invalid. Visit iCalendar.org to view valid
properties and test your feed using the site's iCalendar validator tool.

See the examples folder for programs that read and write iCalendar
files. At its simpliest, you need to include the library at the top of your program:

```php
require_once($path_to_library . "/zapcallib.php");
```

Create an ical object using the ZCiCal object:

```php
$icalobj = new ZCiCal();
```

Add an event object:

```php
$eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);
```

Write the object in iCalendar format using the  export() function call:

```php
echo $icalobj->export();
```

This example will not validate since it is missing some required elements. 
Look at the simpleevent.php example for the minimum # of elements 
needed for a validated iCalendar file.

To read an existing iCalendar file/feed, create the ZCiCal object with a string representing the contents of the iCalendar file:

```php
$icalobj = new ZCiCal($icalstring);
```

