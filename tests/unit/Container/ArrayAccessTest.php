<?php

namespace Container;

use Codeception\TestCase\Test;
use Maslosoft\Gazebo\ConfigContainer;
use Maslosoft\GazeboTest\Model\HardInterface;
use Maslosoft\GazeboTest\Model\SoftInterface;
use Maslosoft\GazeboTest\Model\TestModel;
use UnitTester;

class TestContainer extends ConfigContainer
{

	public $test;
	public $test2;
	public $test3;
	public $test4;
	public $foo;
	public $bar;

}

class ArrayAccessTest extends Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	/**
	 *
	 * @var TestContainer
	 */
	private $container = null;

	// executed before each test
	protected function _before()
	{
		$this->container = new TestContainer();
	}

	// executed after each test
	protected function _after()
	{
		
	}

	public function testCanStoreArrayValues()
	{
		$cfg1 = [
			TestModel::class => [
				SoftInterface::class
			]
		];
		$cfg2 = [
			TestModel::class => [
				HardInterface::class
			]
		];
		$this->container['test'] = $cfg1;
		$this->container['test2'] = $cfg2;
		$this->assertSame($this->container['test'], $cfg1);
		$this->assertSame($this->container['test2'], $cfg2);
	}

	public function testIfCanUnset()
	{
		$this->container['test'] = [
			TestModel::class => [
				SoftInterface::class
			]
		];
		$this->container->test2 = [
			TestModel::class => [
				HardInterface::class
			]
		];

		$this->assertTrue(isset($this->container['test']));
		unset($this->container['test']);
		$this->assertFalse(isset($this->container['test']));

		$this->assertTrue(isset($this->container->test2));
		unset($this->container->test2);
		$this->assertFalse(isset($this->container->test2));
	}

	public function testCanAccessAsFieldValues()
	{
		$cfg1 = [
			TestModel::class => [
				SoftInterface::class
			]
		];
		$cfg2 = [
			TestModel::class => [
				HardInterface::class
			]
		];
		$this->container->test3 = $cfg1;
		$this->container->test4 = $cfg2;
		$this->assertSame($this->container->test3, $cfg1);
		$this->assertSame($this->container->test4, $cfg2);
		$this->assertSame($this->container['test3'], $cfg1);
		$this->assertSame($this->container['test4'], $cfg2);
	}

	public function testCanSerialize()
	{
		$cfg1 = [
			TestModel::class => [
				SoftInterface::class
			]
		];
		$cfg2 = [
			TestModel::class => [
				HardInterface::class
			]
		];
		$this->container->foo = $cfg1;
		$this->container->bar = $cfg2;
		$serialized = serialize($this->container);
		$unserialized = unserialize($serialized);
		$this->assertSame($unserialized['foo'], $cfg1);
		$this->assertSame($unserialized['bar'], $cfg2);
		$this->assertTrue($unserialized instanceof TestContainer);
	}

	public function testCanDoForeach()
	{
		$cfg1 = [
			TestModel::class => [
				SoftInterface::class
			]
		];
		$cfg2 = [
			TestModel::class => [
				HardInterface::class
			]
		];
		$data = [
			'foo' => $cfg1,
			'bar' => $cfg2
		];
		$this->container->foo = $cfg1;
		$this->container->bar = $cfg2;
		foreach ($this->container as $key => $value)
		{
			$this->assertSame($value, $data[$key]);
		}
	}

	public function testCanUnset()
	{
		$cfg1 = [
			TestModel::class => [
				SoftInterface::class
			]
		];
		$cfg2 = [
			TestModel::class => [
				HardInterface::class
			]
		];
		$this->container->foo = $cfg1;
		$this->container->bar = $cfg2;
		unset($this->container['foo']);
		$this->assertFalse(isset($this->container['foo']));
		$this->assertFalse(isset($this->container->foo));
		unset($this->container['bar']);
		$this->assertFalse(isset($this->container['bar']));
		$this->assertFalse(isset($this->container->bar));
	}

	public function testCount()
	{
		$cfg1 = [
			TestModel::class => [
				SoftInterface::class
			]
		];
		$cfg2 = [
			TestModel::class => [
				HardInterface::class
			]
		];
		$this->container->foo = $cfg1;
		$this->container->bar = $cfg2;
		$this->assertSame(count($this->container), 2);
	}

}
