<!--header-->
<!-- Auto generated do not modify between `header` and `/header` -->

# <a href="http://maslosoft.com/gazebo/">Maslosoft Gazebo</a>
<a href="http://maslosoft.com/gazebo/">_Plugin container_</a>

<a href="https://packagist.org/packages/maslosoft/gazebo" title="Latest Stable Version">
<img src="https://poser.pugx.org/maslosoft/gazebo/v/stable.svg" alt="Latest Stable Version" style="height: 20px;"/>
</a>
<a href="https://packagist.org/packages/maslosoft/gazebo" title="License">
<img src="https://poser.pugx.org/maslosoft/gazebo/license.svg" alt="License" style="height: 20px;"/>
</a>
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Maslosoft/Gazebo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Maslosoft/Gazebo/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Maslosoft/Gazebo/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Maslosoft/Gazebo/?branch=master)
<img src="https://travis-ci.org/Maslosoft/Gazebo.svg?branch=master"/>
<img src="http://hhvm.h4cc.de/badge/maslosoft/gazebo.svg?style=flat"/>

### Quick Install
```bash
composer require maslosoft/gazebo:"*"
```

<!--/header-->

### Usage

```php
//Plugins
class WaterPlugin implements WetInterface
{
	public $name = 'foo';
}
class MetalPlugin implements HardInterface
{
	public $options = false;
}
class GenericPlugin
{

}
// Config:
$config = [
			TestModel::class => [
				WaterPlugin::class,
				[
					'class' => MetalPlugin::class,
					'options' => true
				],
				GenericPlugin::class,
			],
		];

// Create plugins but only for selected interfaces
$plugins = (new PluginFactory())->instance($this->config, $model, [
			HardInterface::class,
			WetInterface::class
		]);


var_dump($plugins);

// Created flyweight instances of two plugins
//array(2) {
//	[0] => class Maslosoft\GazeboTest\Model\WaterPlugin#181 (1) {
//		public $name => string(3) "foo"
//	}
//	[1] => class Maslosoft\GazeboTest\Model\MetalPlugin#182 (2) {
//		public $options => bool(true)
//		public $class => string(38) "Maslosoft\GazeboTest\Model\MetalPlugin"
//	}
//}
```