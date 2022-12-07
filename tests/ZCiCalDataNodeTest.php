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
class ZCiCalDataNodeTest extends \PHPUnit\Framework\TestCase
	{
	/**
	 * @dataProvider providerZCiCalDataNode
	 */
	public function testZCiCalDataNode(string $line) : void
		{
		$dataNode = new \ICalendarOrg\ZCiCalDataNode($line);
		$generated = "{$dataNode}";
		$this->assertEquals($line . "\n", $generated, $line . ' has an error');
		}

	/**
	 * Expressions data provider
	 *
	 * @return array<array<string>>
	 */
	public function providerZCiCalDataNode() : array
		{
		return [
			['ACTION:AUDIO'],
			['ATTACH;FMTTYPE=application/postscript:ftp://example.com/pub/conf/bkgrnd.ps'],
			['ATTACH;FMTTYPE=audio/basic:http://example.com/pub/audio-files/ssbanner.aud'],
			['ATTENDEE;PARTSTAT=ACCEPTED:mailto:jqpublic@example.com'],
			['ATTENDEE;RSVP=TRUE:mailto:jsmith@example.com'],
			['ATTENDEE;RSVP=TRUE;ROLE=REQ-PARTICIPANT;CUTYPE=GROUP:mailto:employee-A@example.com'],
			['CATEGORIES:CONFERENCE'],
			['CATEGORIES:MEETING'],
			['CATEGORIES:MEETING,PROJECT'],
			['CATEGORIES:Project Report,XYZ,Weekly Meeting'],
			['CLASS:PUBLIC'],
			['CREATED:19980309T130000Z'],
			['DESCRIPTION:Discuss how we can test c&s interoperability
using iCalendar and other IETF standards.'],
			['DESCRIPTION:Networld+Interop Conference and Exhibit
Atlanta World Congress Center
Atlanta, Georgia'],
			['DESCRIPTION:Project XYZ Review Meeting'],
			['DESCRIPTION:Project xyz Review Meeting Minutes
Agenda
1. Review of project version 1.0 requirements.
2. Definitionof project processes.
3. Review of project schedule.
Participants: John Smith, Jane Doe, Jim Dandy
-It was decided that the requirements need to be signed off by product marketing.
-Project processes were accepted.
-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.
-New schedule will be distributed by Friday.
-Next weeks meeting is cancelled. No meeting until 3/23.'],
			['DTEND:19960920T220000Z'],
			['DTEND:19970324T210000Z'],
			['DTEND:19980410T141711Z'],
			['DTEND;TZID=America/New_York:19980312T093000'],
			['DTSTART:19970324T123000Z'],
			['DTSTART:19980313T141711Z'],
			['DTSTART:19981025T020000'],
			['DTSTART:19990404T020000'],
			['DTSTART;TZID=America/New_York:19980312T083000'],
			['DUE:19980415T000000'],
			['DURATION:PT1H'],
			['FREEBUSY:19980314T233000Z/19980315T003000Z'],
			['LOCATION:1CP Conference Room 4350'],
			['LOCATION:LDB Lobby'],
			['METHOD:xyz'],
			['ORGANIZER:mailto:jdoe@example.com'],
			['PRODID:-//ABC Corporation//NONSGML My Product//EN'],
			['PRODID:-//RDU Software//NONSGML HandCal//EN'],
			['PRODID:-//xyz Corp//NONSGML PDA Calendar Version 1.0//EN'],
			['REPEAT:4'],
			['SEQUENCE:0'],
			['SEQUENCE:2'],
			['STATUS:CONFIRMED'],
			['STATUS:DRAFT'],
			['STATUS:NEEDS-ACTION'],
			['SUMMARY:Calendaring Interoperability Planning Meeting'],
			['SUMMARY:Networld+Interop Conference'],
			['SUMMARY:Submit Income Taxes'],
			['SUMMARY:XYZ Project Review'],
			['TRIGGER:19980403T120000Z'],
			['TZID:America/New_York'],
			['TZNAME:EDT'],
			['TZNAME:EST'],
			['TZOFFSETFROM:-0400'],
			['TZOFFSETFROM:-0500'],
			['TZOFFSETTO:-0400'],
			['TZOFFSETTO:-0500'],
			['UID:guid-1.example.com'],
			['UID:uid1@example.com'],
			['URL:http://www.example.com/calendar/busytime/jsmith.ifb'],
			['VERSION:2.0'],
		];
		}
	}
