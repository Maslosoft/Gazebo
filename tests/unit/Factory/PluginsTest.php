<?php

namespace Factory;

use Codeception\TestCase\Test;
use Maslosoft\Gazebo\PluginFactory;
use Maslosoft\GazeboTest\Model\HardInterface;
use Maslosoft\GazeboTest\Model\SoftInterface;
use Maslosoft\GazeboTest\Model\TestModel;
use Maslosoft\GazeboTest\Model\TestPluginOne;
use Maslosoft\GazeboTest\Model\TestPluginTwo;
use Maslosoft\GazeboTest\Model\WetInterface;
use UnitTester;

class PluginsTest extends Test
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

	// tests
	public function testIfWillConfigureModels()
	{
		$model = new TestModel;
		$plugins = (new PluginFactory())->create($this->config, $model);
		$this->assertSame(2, count($plugins), 'Should create 2 plugin instances');
		$expectedCfg = $this->config[TestModel::class];

		$this->assertInstanceOf($expectedCfg[0], $plugins[0]);
		$this->assertInstanceOf($expectedCfg[1]['class'], $plugins[1]);
		$this->assertSame($expectedCfg[1]['options'], $plugins[1]->options);
	}

	public function testIfWillConfigureModelsWithSelectedInterface()
	{
		// Direct interface
		$model = new TestModel;
		$plugins = (new PluginFactory())->create($this->config, $model, WetInterface::class);
		$this->assertSame(1, count($plugins), 'Should create 1 plugin instance');
		$expectedCfg = $this->config[TestModel::class];

		$this->assertInstanceOf($expectedCfg[0], $plugins[0]);
	}

	public function testIfWillConfigureModelsWithSelectedParentInterface()
	{
		// Parent interface
		$model = new TestModel;
		$plugins = (new PluginFactory())->create($this->config, $model, SoftInterface::class);
		$this->assertSame(1, count($plugins), 'Should create 1 plugin instance');
		$expectedCfg = $this->config[TestModel::class];

		$this->assertInstanceOf($expectedCfg[0], $plugins[0]);
	}

	public function testIfWillConfigureModelsWithTwoSelectedInterfaces()
	{
		$model = new TestModel;
		$plugins = (new PluginFactory())->create($this->config, $model, [
			SoftInterface::class,
			HardInterface::class
		]);
		$this->assertSame(2, count($plugins), 'Should create 2 plugin instances');
		$expectedCfg = $this->config[TestModel::class];

		$this->assertInstanceOf(SoftInterface::class, $plugins[0]);
		$this->assertInstanceOf(HardInterface::class, $plugins[1]);
		$this->assertSame($expectedCfg[1]['options'], $plugins[1]->options);
	}

}
