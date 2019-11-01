<?php

/**
 * This file is part of the ICalendarOrg package
 *
 * (c) Bruce Wells
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source
 * code
 *
 */
use ICalendarOrg\ZDateHelper;

class DateTest extends \PHPUnit\Framework\TestCase
	{

	public function testAddDate() : void
		{
		$this->assertEquals(strtotime('2019-10-29'), ZDateHelper::addDate(strtotime('2019-10-30'), 0, 0, 0, 0, -1, 0));
		$this->assertEquals(strtotime('2019-10-31'), ZDateHelper::addDate(strtotime('2019-10-30'), 0, 0, 0, 0, 1, 0));
		$this->assertEquals(strtotime('2019-10-31 01:00:00'), ZDateHelper::addDate(strtotime('2019-10-30'), 1, 0, 0, 0, 1, 0));
		$this->assertEquals(strtotime('2019-10-31 01:01:00'), ZDateHelper::addDate(strtotime('2019-10-30'), 1, 1, 0, 0, 1, 0));
		$this->assertEquals(strtotime('2019-10-31 01:01:10'), ZDateHelper::addDate(strtotime('2019-10-30'), 1, 1, 10, 0, 1, 0));
		$this->assertEquals(strtotime('2018-10-31 01:01:10'), ZDateHelper::addDate(strtotime('2019-10-30'), 1, 1, 10, 0, 1, -1));
		$this->assertEquals(strtotime('2020-10-31 01:01:10'), ZDateHelper::addDate(strtotime('2019-10-30'), 1, 1, 10, 0, 1, 1));
		$this->assertEquals(strtotime('2020-10-31 01:00:50'), ZDateHelper::addDate(strtotime('2019-10-30'), 1, 1, -10, 0, 1, 1));
		$this->assertEquals(strtotime('2020-10-30 22:00:50'), ZDateHelper::addDate(strtotime('2019-10-30'), -2, 1, -10, 0, 1, 1));
		}

	public function testDayInMonth() : void
		{
		$this->assertEquals(31, ZDateHelper::dayInMonth(1, 2019));
		$this->assertEquals(28, ZDateHelper::dayInMonth(2, 2019));
		$this->assertEquals(29, ZDateHelper::dayInMonth(2, 2020));
		$this->assertEquals(31, ZDateHelper::dayInMonth(3, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(4, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(5, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(6, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(7, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(8, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(9, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(10, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(11, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(12, 2019));
		}

	public function testFromiCaltoUnixDateTime() : void
		{
		$this->assertEquals(strtotime('2019-1-1'), ZDateHelper::fromiCaltoUnixDateTime('20190101'));
		$this->assertEquals(strtotime('2019-1-1 01:02:03'), ZDateHelper::fromiCaltoUnixDateTime('20190101T010203'));
		$this->assertEquals(strtotime('2019-1-1 01:02:03'), ZDateHelper::fromiCaltoUnixDateTime('20190101T010203Z'));
		$this->assertEquals(strtotime('2020-3-1 01:02:03'), ZDateHelper::fromiCaltoUnixDateTime('20200301T010203Z'));
		$this->assertEquals(strtotime('2040-3-1 03:02:01'), ZDateHelper::fromiCaltoUnixDateTime('20400301T030201Z'));
		}

	public function testFromUnixDateTimetoiCal() : void
		{
		$this->assertEquals('20190101T010203', ZDateHelper::fromUnixDateTimetoiCal(strtotime('2019-1-1 01:02:03')));
		$this->assertEquals('20200301T010203', ZDateHelper::fromUnixDateTimetoiCal(strtotime('2020-3-1 01:02:03')));
		$this->assertEquals('20400301T030201', ZDateHelper::fromUnixDateTimetoiCal(strtotime('2040-3-1 03:02:01')));
		}

	public function testGetAbsDate() : void
		{
		$this->assertEquals('2019-01-01 01:02:03', ZDateHelper::getAbsDate('+1d', '2018-12-31 01:02:03'));
		$this->assertEquals('2019-12-31 01:02:03', ZDateHelper::getAbsDate('+1y', '2018-12-31 01:02:03'));
		$this->assertEquals('2019-01-31 01:02:03', ZDateHelper::getAbsDate('+1m', '2018-12-31 01:02:03'));
		$this->assertEquals('2018-12-31 02:02:03', ZDateHelper::getAbsDate('+1h', '2018-12-31 01:02:03'));
		$this->assertEquals('2018-12-31 01:03:03', ZDateHelper::getAbsDate('+1n', '2018-12-31 01:02:03'));

		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1d', '2019-01-01 01:02:03'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1y', '2019-12-31 01:02:03'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1m', '2019-01-31 01:02:03'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1h', '2018-12-31 02:02:03'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1n', '2018-12-31 01:03:03'));

		$this->assertEquals('2019-01-01 02:02:03', ZDateHelper::getAbsDate('+1d,+1h', '2018-12-31 01:02:03'));
		$this->assertEquals('2019-01-01 02:02:04', ZDateHelper::getAbsDate('+1d,+1h,+1s', '2018-12-31 01:02:03'));
		$this->assertEquals('2019-01-01 02:03:04', ZDateHelper::getAbsDate('+1d,+1h,+1n,+1s', '2018-12-31 01:02:03'));
		$this->assertEquals('2019-02-01 02:03:04', ZDateHelper::getAbsDate('+1m,+1d,+1h,+1n,+1s', '2018-12-31 01:02:03'));
		$this->assertEquals('2020-02-01 02:03:04', ZDateHelper::getAbsDate('+1y,+1m,+1d,+1h,+1n,+1s', '2018-12-31 01:02:03'));
		$this->assertEquals('2029-02-01 02:03:04', ZDateHelper::getAbsDate('+10y,+1m,+1d,+1h,+1n,+1s', '2018-12-31 01:02:03'));
		$this->assertEquals('2049-02-01 02:03:04', ZDateHelper::getAbsDate('+30y,+1m,+1d,+1h,+1n,+1s', '2018-12-31 01:02:03'));

		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1d,-1h', '2019-01-01 02:02:03'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1d,-1h,-1s', '2019-01-01 02:02:04'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1d,-1h,-1n,-1s', '2019-01-01 02:03:04'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1m,-1d,-1h,-1n,-1s', '2019-02-01 02:03:04'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-1y,-1m,-1d,-1h,-1n,-1s', '2020-02-01 02:03:04'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-10y,-1m,-1d,-1h,-1n,-1s', '2029-02-01 02:03:04'));
		$this->assertEquals('2018-12-31 01:02:03', ZDateHelper::getAbsDate('-30y,-1m,-1d,-1h,-1n,-1s', '2049-02-01 02:03:04'));
		}

	public function testGetDateFromDay() : void
		{
		$stamp = strtotime('2019-10-30');
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, -1, 3));
		$stamp = strtotime('2019-9-1');
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, 0, 0));
		$stamp = strtotime('2019-9-8');
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, 1, 0));
		$stamp = strtotime('2019-10-1');
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, 0, 2));
		$stamp = strtotime('2019-10-4');
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, 0, 5));
		$stamp = strtotime('2019-10-10');
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, 1, 4));
		$stamp = strtotime('2019-10-31');
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, -1, 4));
		$this->assertEquals($stamp, ZDateHelper::getDateFromDay($stamp, 4, 4));
		}

	public function testIsToday() : void
		{
		$this->assertEquals(true, ZDateHelper::isToday(time()));
		$this->assertEquals(false, ZDateHelper::isToday(strtotime('2019-10-30')));
		$this->assertEquals(false, ZDateHelper::isToday(strtotime('2222-10-30')));
		}

	public function testInDay() : void
		{
		$stamp = strtotime('2222-10-30');
		$this->assertEquals(true, ZDateHelper::inDay($stamp, $stamp, $stamp));
		$this->assertEquals(true, ZDateHelper::inDay($stamp, $stamp - 1, $stamp + 1));
		$this->assertEquals(false, ZDateHelper::inDay($stamp + 2, $stamp - 1, $stamp + 1));
		}

	public function testIsAfterToday() : void
		{
		$this->assertEquals(false, ZDateHelper::isAfterToday(strtotime('2019-10-31')));
		$this->assertEquals(false, ZDateHelper::isAfterToday(time()));
		$this->assertEquals(true, ZDateHelper::isAfterToday(time() + 60 * 60 * 24));
		$this->assertEquals(true, ZDateHelper::isAfterToday(strtotime('2035-10-30')));
		$this->assertEquals(true, ZDateHelper::isAfterToday(strtotime('2055-10-30')));
		}

	public function testIsBeforeToday() : void
		{
		$this->assertEquals(true, ZDateHelper::isBeforeToday(strtotime('2019-10-30')));
		$this->assertEquals(false, ZDateHelper::isBeforeToday(time()));
		$this->assertEquals(false, ZDateHelper::isBeforeToday(strtotime('2035-10-30')));
		$this->assertEquals(false, ZDateHelper::isBeforeToday(strtotime('2055-10-30')));
		}

	public function testIsFuture() : void
		{
		$this->assertEquals(false, ZDateHelper::isFuture(strtotime('2019-10-31')));
		$this->assertEquals(false, ZDateHelper::isFuture(date('Y-m-d')));
		$this->assertEquals(true, ZDateHelper::isFuture(strtotime('2222-10-30')));
		}

	public function testIsPast() : void
		{
		$this->assertEquals(true, ZDateHelper::isPast(strtotime('2019-10-30')));
		$this->assertEquals(true, ZDateHelper::isPast(date('Y-m-d')));
		$this->assertEquals(false, ZDateHelper::isPast(strtotime('2222-10-30')));
		}

	public function testIsTomorrow() : void
		{
		$this->assertEquals(false, ZDateHelper::isTomorrow(time()));
		$this->assertEquals(false, ZDateHelper::isTomorrow(strtotime('2019-10-30')));
		$this->assertEquals(true, ZDateHelper::isTomorrow(time() + 60 * 60 *24));
		$this->assertEquals(false, ZDateHelper::isTomorrow(strtotime('2222-10-30')));
		}

	public function testIsWeekend() : void
		{
		$this->assertEquals(false, ZDateHelper::isWeekend(strtotime('2019-10-31')));
		$this->assertEquals(false, ZDateHelper::isWeekend(strtotime('2019-11-01')));
		$this->assertEquals(true, ZDateHelper::isWeekend(strtotime('2019-11-02')));
		$this->assertEquals(true, ZDateHelper::isWeekend(strtotime('2019-11-02')));
		$this->assertEquals(false, ZDateHelper::isWeekend(strtotime('2019-11-04')));
		}

	public function testToiCalDate() : void
		{
		$this->assertEquals('20191104', ZDateHelper::toiCalDate(strtotime('2019-11-04')));
		}

	public function testToiCalDateTime() : void
		{
		$this->assertEquals('20191104T121314', ZDateHelper::toiCalDateTime(strtotime('2019-11-04 12:13:14')));
		}

	public function testToLocalDateTime() : void
		{
		$time = '2019-11-11 23:59:59';
		$this->assertEquals($time, ZDateHelper::toLocalDateTime($time));
		$this->assertNotEquals($time, ZDateHelper::toLocalDateTime($time, 'EST'));
		$this->assertEquals($time, ZDateHelper::toLocalDateTime($time, 'FRED'));
		$this->assertEquals($time, ZDateHelper::toLocalDateTime('2019-11-12 04:59:59', 'EST'));
		}

	public function testToSQLDate() : void
		{
		$date = '2019-11-04';
		$this->assertEquals($date, ZDateHelper::toSQLDate(strtotime($date)));
		}

	public function testToSQLDateTime() : void
		{
		$date = '2019-11-04 23:59:59';
		$this->assertEquals($date, ZDateHelper::toSQLDateTime(strtotime($date)));
		}

	public function testToUnixDate() : void
		{
		$date = '2019-11-04';
		$this->assertEquals(strtotime($date), ZDateHelper::toUnixDate($date));
		$this->assertEquals(strtotime($date), ZDateHelper::toUnixDate($date . ' 23:59:59'));
		}

	public function testToUnixDateTime() : void
		{
		$date = '2019-11-04';
		$this->assertEquals(strtotime($date), ZDateHelper::toUnixDateTime($date));
		$date = '2019-11-04 23:59:59';
		$this->assertEquals(strtotime($date), ZDateHelper::toUnixDateTime($date));
		}

	public function testToUTCDateTime() : void
		{
		$time = '2019-11-11 23:59:59';
		$this->assertEquals($time, ZDateHelper::toUTCDateTime($time));
		$this->assertNotEquals($time, ZDateHelper::toUTCDateTime($time, 'EST'));
		$this->assertEquals($time, ZDateHelper::toUTCDateTime($time, 'FRED'));
		$this->assertEquals('2019-11-12 04:59:59', ZDateHelper::toUTCDateTime($time, 'EST'));
		}

	}

