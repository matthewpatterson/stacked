<?php

namespace Stacked;

use PHPUnit_Framework_TestCase;

require_once('../src/Stacked/Trace.php');

class TraceTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var Trace
	 */
	protected $object;

	protected function createObject($limit = 0) {
		$e = function($L = 0) { return Trace::start($L); };
		$d = function($L = 0) use ($e) { return $e($L); };
		$c = function($L = 0) use ($d) { return $d($L); };
		$b = function($L = 0) use ($c) { return $c($L); };
		$a = function($L = 0) use ($b) { return $b($L); };

		$this->object = $a($limit);
	}

	public function testStart() {
		$this->createObject();
		$this->assertInstanceOf('\\Stacked\\Trace', $this->object);
	}

	public function testStartWithLimit() {
		$this->createObject(2);

		// Check our head instance
		$this->assertInstanceOf('\\Stacked\\Trace', $this->object);

		// Check previous calls which should exist
		$this->assertInstanceOf('\\Stacked\\Trace', $this->object->previous);
		$this->assertNotNull($this->object->previous->line);

		// Check previous calls beyond the limit
		$this->assertInstanceOf('\\Stacked\\Trace', $this->object->previous->previous);
		$this->assertNull($this->object->previous->previous->line);
	}

	public function testReverseNavigation() {
		$this->createObject();
		$this->assertSame($this->object->previous, $this->object->previous->previous->next);
	}

	public function testClass() {
		$this->createObject();

		// Test class known to exist
		$actual = $this->object->notClass('TraceTest');
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertNotEquals('\\Stacked\Trace', $actual->class);

		// Test class not found in our trace
		$actual = $this->object->class('ClassDoesNotExist');
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertNull($actual->class);
	}

	public function testFile() {
		$this->createObject();

		// Test file known to exist
		$actual = $this->object->notFile('TraceTest.php');
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertNotEquals(__FILE__, $actual->file);

		// Test file not found in our trace
		$actual = $this->object->file('FileDoesNotExist.php');
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertNull($actual->file);
	}

	public function testFunction() {
		$this->createObject();

		// Test function known to exist
		$actual = $this->object->function('createObject')->notFunction('createObject');
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertEquals('testFunction', $actual->function);

		// Test function not found in our trace
		$actual = $this->object->function('functionDoesNotExist');
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertNull($actual->function);
	}

	public function testLine() {
		$this->createObject();

		// Test line known to exist
		$actual = $this->object->line(19)->notLine(19); // Where $c() is declared
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertEquals(20, $actual->line);

		// Test line not found in our trace
		$actual = $this->object->line(999999);
		$this->assertInstanceOf('\\Stacked\\Trace', $actual);
		$this->assertNull($actual->line);
	}

}
