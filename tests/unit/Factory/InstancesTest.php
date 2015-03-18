<?php

namespace Factory;

use Codeception\TestCase\Test;
use Maslosoft\Gazebo\PluginFactory;
use Maslosoft\GazeboTest\Model\TestModel;
use Maslosoft\GazeboTest\Model\TestPluginOne;
use Maslosoft\GazeboTest\Model\TestPluginTwo;
use UnitTester;

class InstancesTest extends Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	/**
	 * Test Configuration
	 * @var mixed[][]
	 */
	private $config = [];

	protected function _before()
	{
		$this->config = [
			TestModel::class => [
				TestPluginOne::class,
				[
					'class' => TestPluginTwo::class,
					'options' => true
				]
			]
		];
	}

	public function testIfWillConfigureModels()
	{
		$model = new TestModel;
		$plugins = (new PluginFactory())->instance($this->config, $model);
		$this->assertSame(2, count($plugins), 'Should create 2 plugin instances');

		// Now configure second instance
		$plugins2 = (new PluginFactory())->instance($this->config, $model);
		$this->assertSame(2, count($plugins2), 'Should create 2 plugin instances');

		// Default config check
		$expectedCfg = $this->config[TestModel::class];

		$this->assertInstanceOf($expectedCfg[0], $plugins2[0]);
		$this->assertInstanceOf($expectedCfg[1]['class'], $plugins2[1]);
		$this->assertSame($expectedCfg[1]['options'], $plugins2[1]->options);

		// Check if same instances
		$this->assertSame($plugins[0], $plugins2[0]);
		$this->assertSame($plugins[1], $plugins2[1]);
	}

}
