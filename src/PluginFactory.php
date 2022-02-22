<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/gazebo
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 *
 */

namespace Maslosoft\Gazebo;

use Generator;
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Gazebo\Exceptions\ConfigurationException;
use Maslosoft\Gazebo\Storage\PluginsStorage;
use Maslosoft\Gazebo\Storage\PluginStorage;
use ReflectionClass;

/**
 * PluginFactory
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PluginFactory
{

	/**
	 * Configured plugins instances
	 * @var PluginsStorage
	 */
	private PluginsStorage $instances;

	/**
	 * Single plugins instances
	 * @var PluginStorage
	 */
	private PluginStorage $plugins;

	/**
	 * EmbeDi instance
	 * @var EmbeDi
	 */
	private EmbeDi $di;

	/**
	 * Static instances of plugin factories
	 * @var PluginFactory[]
	 */
	private static $_pf = [];

	/**
	 * Class constructor with optional instanceid which is passed to EmbeDi
	 * @param string $instanceId
	 */
	public function __construct(string $instanceId = Gazebo::DefaultInstanceId)
	{
		$this->instances = new PluginsStorage($this, $instanceId);
		$this->plugins = new PluginStorage($this, $instanceId);
		$this->di = new EmbeDi($instanceId);
	}

	/**
	 * Flyweight accessor for `PluginFactory` with optional instanceid.
	 * This will create only one runtime wide instance of `PluginFactory` for each `$instanceId`.
	 * @param string $instanceId
	 * @return PluginFactory Flyweight instance of PluginFactory
	 */
	public static function fly(string $instanceId = Gazebo::DefaultInstanceId)
	{
		if (empty(self::$_pf[$instanceId]))
		{
			self::$_pf[$instanceId] = new static($instanceId);
		}
		return self::$_pf[$instanceId];
	}

	/**
	 * Create plugin set from `$configuration` for `$object`
	 * optionally implementing one or more `$interfaces`.
	 *
	 * @param PluginsContainer|array[][] $configuration Configuration arrays
	 * @param string|object $object Object or class name
	 * @param null|string|string[] $interfaces Array or string of interface names or class names
	 * @return object[] Array of plugin instances
	 */
	public function create($configuration, $object, $interfaces = null): array
	{
		$plugins = [];
		foreach ($this->_getConfigs($configuration, $object, $interfaces) as $config)
		{
			$plugins[] = $this->_instantiate($config);
		}
		return $plugins;
	}

	/**
	 * Get instance of plugin set from `$config` for `$object`
	 * optionally implementing one or more `$interfaces`.
	 *
	 * This will create instances unique for each object and interfaces set.
	 * This will create only **one instance** of each plugin per config.
	 *
	 * @param PluginsContainer|array[][] $config
	 * @param string|object $object
	 * @param null|string|string[] $interfaces
	 * @return object[] Array of plugin instances
	 */
	public function instance($config, $object, $interfaces = null)
	{
		if (is_string($object))
		{
			$key = $object;
		}
		else
		{
			$key = get_class($object);
		}
		if (null !== $interfaces)
		{
			if (!is_array($interfaces))
			{
				$interfaces = [
					(string) $interfaces
				];
			}
			$key .= '.' . implode('.', $interfaces);
		}
		$key .= $this->getKey($config);
		if (!isset($this->instances[$key]))
		{
			$plugins = [];
			foreach ($this->_getConfigs($config, $object, $interfaces) as $oneConfig)
			{
				$plugins[] = $this->_instantiate($oneConfig, true);
			}
			$this->instances[$key] = $plugins;
		}
		return $this->instances[$key];
	}

	/**
	 * Check if object or class implements one or more interfaces
	 * @param object|string $object
	 * @param null|string|string[] $interfaces Interfaces to check
	 * @return boolean
	 */
	private function _implements($object, $interfaces = null): bool
	{
		if (null === $interfaces)
		{
			return true;
		}
		if (!is_array($interfaces))
		{
			$interfaces = [
				(string) $interfaces
			];
		}
		foreach ($interfaces as $interface)
		{
			$objectInfo = new ReflectionClass($object);
			$interfaceInfo = new ReflectionClass($interface);
			if ($objectInfo->name === $interfaceInfo->name)
			{
				return true;
			}
			if ($objectInfo->isSubclassOf($interface))
			{
				return true;
			}
			if ($interfaceInfo->isInterface() && $objectInfo->implementsInterface($interface))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Get class name from configuration
	 * @param string|mixed[] $config
	 * @return string
	 */
	private function _getClassName($config): string
	{
		if (is_string($config))
		{
			return $config;
		}
		return $config[$this->di->classField];
	}

	/**
	 * Config generator
	 *
	 * @param PluginsContainer|array[][] $configuration Configuration arrays
	 * @param string|object $object Object or class name
	 * @param null|string|string[] $interfaces Array or string of interface names or class names
	 * @yield mixed[] Array of plugin configs
	 */
	private function _getConfigs($configuration, $object, $interfaces = null): Generator
	{
		foreach ($configuration as $interface => $configs)
		{
			if (!is_string($interface))
			{
				throw new ConfigurationException(sprintf('Wrong configuration for key `%s` - key must be class name', $interface));
			}
			if (!is_array($configs))
			{
				throw new ConfigurationException(sprintf('Wrong configuration for key `%s`, configuration should be array, `%s` given', $interface, gettype($configs)));
			}
			if (!$this->_implements($object, $interface))
			{
				continue;
			}
			foreach ($configs as $config)
			{
				$pluginClass = $this->_getClassName($config);
				if (!$this->_implements($pluginClass, $interfaces))
				{
					continue;
				}
				yield $config;
			}
		}
	}

	/**
	 * Instantiate object based on configuration
	 * @param string|mixed[] $config
	 * @return object
	 */
	private function _instantiate($config, $fly = false)
	{
		$className = $this->_getClassName($config);
		if ($fly)
		{
			$key = $this->getKey($config);
			if (isset($this->plugins[$key]))
			{
				$plugin = $this->plugins[$key];
			}
			else
			{
				$plugin = $this->plugins[$key] = new $className;
			}
		}
		else
		{
			$plugin = new $className;
		}
		if (is_array($config))
		{
			$plugin = $this->di->apply($config, $plugin);
		}
		return $plugin;
	}

	private function getKey($config): string
	{
		if (is_array($config))
		{
			// Only class field, use class name as a key
			if (!empty($config['class']) && count($config) === 1)
			{
				return $config['class'];
			}

			// Complex config
			return json_encode($config, JSON_THROW_ON_ERROR);
		}

		// Scalar config, ie class name
		return $config;
	}

}
