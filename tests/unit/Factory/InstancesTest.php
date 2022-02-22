<?php

namespace Factory;

use Codeception\TestCase\Test;
use Maslosoft\Gazebo\PluginFactory;
use Maslosoft\GazeboTest\Model\HardInterface;
use Maslosoft\GazeboTest\Model\SoftInterface;
use Maslosoft\GazeboTest\Model\TestModel;
use Maslosoft\GazeboTest\Model\WaterPlugin;
use Maslosoft\GazeboTest\Model\GenericPlugin;
use Maslosoft\GazeboTest\Model\MetalPlugin;
use Maslosoft\GazeboTest\Model\WetInterface;
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
				WaterPlugin::class,
				[
					'class' => MetalPlugin::class,
					'options' => true
				],
				GenericPlugin::class,
			],
		];
	}

	public function testIfWillConfigureModels()
	{
		$model = new TestModel;
		$plugins = (new PluginFactory())->instance($this->config, $model);
		$this->assertSame(3, count($plugins), 'Should create 3 plugin instances');

		// Now configure second instance
		$plugins2 = (new PluginFactory())->instance($this->config, $model);
		$this->assertSame(3, count($plugins2), 'Should create 3 plugin instances');

		// Default config check
		$expectedCfg = $this->config[TestModel::class];

		$this->assertInstanceOf($expectedCfg[0], $plugins2[0]);
		$this->assertInstanceOf($expectedCfg[1]['class'], $plugins2[1]);
		$this->assertInstanceOf($expectedCfg[2], $plugins2[2]);
		$this->assertSame($expectedCfg[1]['options'], $plugins2[1]->options);

		// Check if same instances
		$this->assertSame($plugins[0], $plugins2[0]);
		$this->assertSame($plugins[1], $plugins2[1]);
	}

	public function testIfWillConfigureModelsWithSelectedInterfaces()
	{
		$model = new TestModel;
		$plugins = (new PluginFactory())->instance($this->config, $model, [
			HardInterface::class,
			WetInterface::class
		]);
		$this->assertSame(2, count($plugins), 'Should create 2 plugin instances');

		// Now configure second instance
		$plugins2 = (new PluginFactory())->instance($this->config, $model, [
			HardInterface::class,
			WetInterface::class
		]);
		$this->assertSame(2, count($plugins2), 'Should create 2 plugin instances');

		// Now configure third instance but with one interface only
		$plugins3 = (new PluginFactory())->instance($this->config, $model, [
			HardInterface::class,
		]);
		$this->assertSame(1, count($plugins3), 'Should create 1 plugin instances');

		// Default config check
		$expectedCfg = $this->config[TestModel::class];

		$this->assertInstanceOf($expectedCfg[0], $plugins2[0]);
		$this->assertInstanceOf($expectedCfg[1]['class'], $plugins2[1]);
		$this->assertSame($expectedCfg[1]['options'], $plugins2[1]->options);
		
		// Check if same instances
		$this->assertSame($plugins[0], $plugins2[0]);
		$this->assertSame($plugins[1], $plugins2[1]);
		
		$this->assertSame($plugins[1], $plugins3[0]);
		$this->assertSame($plugins2[1], $plugins3[0]);
	}

}
