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

use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Gazebo\Exceptions\ConfigurationException;
use Maslosoft\Gazebo\Storage\PluginsStorage;
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
	private $instances = null;

	/**
	 * Single plugins instances
	 * @var Storage\PluginStorage
	 */
	private $plugins = null;

	/**
	 * EmbeDi instance
	 * @var EmbeDi
	 */
	private $di = null;

	/**
	 * Class constructor with optional instanceid which is passed to EmbeDi
	 * @param string $instanceId
	 */
	public function __construct($instanceId = Gazebo::DefaultInstanceId)
	{
		$this->instances = new PluginsStorage($this, $instanceId);
		$this->plugins = new Storage\PluginStorage($this, $instanceId);
		$this->di = new EmbeDi($instanceId);
	}

	/**
	 * Create plugin set from `$configuration` for `$object`
	 * optionally implementing one or more `$interfaces`.
	 * 
	 * @param mixed[][] $configuration Configuration arrays
	 * @param string|object $object Object or class name
	 * @param null|string|string[] $interfaces Array or string of interface names or class names
	 * @return object[] Array of plugin instances
	 */
	public function create($configuration, $object, $interfaces = null)
	{
		$plugins = [];
		foreach ($this->_getConfigs($configuration, $object, $interfaces) as $config)
		{
			$plugins[] = $this->_instantiate($config);
		}
		return $plugins;
	}

	/**
	 * Get instance of plugin set from `$configuration` for `$object`
	 * optionally implementing one or more `$interfaces`.
	 *
	 * This will create instances unique for each object and interfaces set.
	 * This will create only **one instance** of each plugin.
	 *
	 * @param mixed[][] $configuration
	 * @param object $object
	 * @param null|string|string[] $interfaces
	 * @return object[] Array of plugin instances
	 */
	public function instance($configuration, $object, $interfaces = null)
	{
		$key = get_class($object);
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
		if (!isset($this->instances[$key]))
		{
			$plugins = [];
			foreach ($this->_getConfigs($configuration, $object, $interfaces) as $config)
			{
				$plugins[] = $this->_instantiate($config, true);
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
	private function _implements($object, $interfaces = null)
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
	private function _getClassName($config)
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
	 * @param mixed[][] $configuration Configuration arrays
	 * @param string|object $object Object or class name
	 * @param null|string|string[] $interfaces Array or string of interface names or class names
	 * @return object[] Array of plugin instances
	 */
	private function _getConfigs($configuration, $object, $interfaces = null)
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
			if(isset($this->plugins[$className]))
			{
				$plugin = $this->plugins[$className];
			}
			else
			{
				$plugin = $this->plugins[$className] = new $className;
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

}
