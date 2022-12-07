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
class FileTest extends \PHPUnit\Framework\TestCase
	{
	/**
	 * @dataProvider providerICSFiles
	 */
	public function testICSFiles(string $contents, string $file) : void
		{
		$this->assertNotEmpty($contents);
		$calendar = new \ICalendarOrg\ZCiCal($contents);
		$generated = $calendar->export();
		$this->assertEquals($this->clean($contents), $this->clean($generated), $file . ' has an error');
//		$this->assertEquals($contents, $generated, $file . ' has an error');
		}

	/**
	 * Expressions data provider
	 *
	 * Test all files in examples directory
	 *
	 * @return array<array<string, string>>
	 */
	public function providerICSFiles() : array
		{
		$iterator = new \DirectoryIterator(__DIR__ . '/examples');

		$contents = [];

		foreach ($iterator as $item)
			{
			if ($item->isFile())
				{
				$fileName = $item->getPathName();
				$contents[] = ['contents' => \file_get_contents($fileName), 'file' => $fileName];
				}
			}

		return $contents;
		}

	private function clean(string $text) : string
		{
		$text = \str_replace("\r", '', $text);

		while (\strpos($text, '  '))
			{
			$text = \str_replace('  ', ' ', $text);
			}

		return $text;
		}
	}
