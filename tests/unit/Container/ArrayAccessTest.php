<?php
namespace Container;

class TestContainer extends \Maslosoft\Gazebo\PluginContainer
{
	public $test;
	public $test2;
	public $test3;
	public $test4;
	public $foo;
	public $bar;
}

class ArrayAccessTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
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
		$this->container['test'] = 'foo';
		$this->container['test2'] = 'bar';
		$this->assertSame($this->container['test'], 'foo');
		$this->assertSame($this->container['test2'], 'bar');
	}

	public function testIfCanUnset()
	{
		$this->container['test'] = 'foo';
		$this->container->test2 = 'bar';
		
		$this->assertTrue(isset($this->container['test']));
		unset($this->container['test']);
		$this->assertFalse(isset($this->container['test']));

		$this->assertTrue(isset($this->container->test2));
		unset($this->container->test2);
		$this->assertFalse(isset($this->container->test2));
	}

	public function testCanAccessAsFieldValues()
	{
		$this->container->test3 = 'foo';
		$this->container->test4 = 'bar';
		$this->assertSame($this->container->test3, 'foo');
		$this->assertSame($this->container->test4, 'bar');
		$this->assertSame($this->container['test3'], 'foo');
		$this->assertSame($this->container['test4'], 'bar');
	}

	public function testCanSerialize()
	{
		$data = [
			'foo' => 1,
			'bar' => 'baz'
		];
		$this->container->foo = $data['foo'];
		$this->container->bar = $data['bar'];
		$serialized = serialize($this->container);
		$unserialized = unserialize($serialized);
		$this->assertSame($unserialized['foo'], $data['foo']);
		$this->assertSame($unserialized['bar'], $data['bar']);
		$this->assertTrue($unserialized instanceof TestContainer);
	}

	public function testCanDoForeach()
	{
		$data = [
			'foo' => 1,
			'bar' => 'baz'
		];
		$this->container->foo = $data['foo'];
		$this->container->bar = $data['bar'];
		foreach ($this->container as $key => $value)
		{
			$this->assertSame($value, $data[$key]);
		}
	}

	public function testCanUnset()
	{
		$data = [
			'foo' => 1,
			'bar' => 'baz'
		];
		$this->container->foo = $data['foo'];
		$this->container->bar = $data['bar'];
		unset($this->container['foo']);
		$this->assertFalse(isset($this->container['foo']));
		$this->assertFalse(isset($this->container->foo));
		unset($this->container['bar']);
		$this->assertFalse(isset($this->container['bar']));
		$this->assertFalse(isset($this->container->bar));
	}

	public function testCount()
	{
		$data = [
			'foo' => 1,
			'bar' => 'baz'
		];
		$this->container->foo = $data['foo'];
		$this->container->bar = $data['bar'];
		$this->assertSame(count($this->container), 2);
	} 
}