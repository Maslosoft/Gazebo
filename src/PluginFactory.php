<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Gazebo;

use Maslosoft\EmbeDi\EmbeDi;
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
	 * Plugins instances
	 * @var PluginsStorage
	 */
	private $instances = null;

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
		$this->di = new EmbeDi($instanceId);
	}

	/**
	 * Create plugin set from `$configuration` for `$object`
	 * optionally implementing one or more `$interfaces`.
	 * 
	 * @param mixed[][] $configuration Configuration arrays
	 * @param string|object $object Object or class name
	 * @param string|string[] $interfaces Array or string of interface names or class names
	 * @return object[] Array of plugin instances
	 */
	public function create($configuration, $object, $interfaces = null)
	{
		$plugins = [];
		foreach ($configuration as $interface => $config)
		{
			if ($this->_implements($object, $interface) && $this->_implements($interface, $interfaces))
			{
				$plugins[] = $this->_instantiate($config);
			}
		}
		return $plugins;
	}

	/**
	 * Get instance of plugin set from `$configuration` for `$object`
	 * optionally implementing one or more `$interfaces`.
	 *
	 * @param mixed[][] $configuration
	 * @param object $object
	 * @param string|string[] $interfaces
	 * @return object[] Array of plugin instances
	 */
	public function instance($configuration, $object, $interfaces = null)
	{
		$key = get_class($object);
		if (null !== $interfaces)
		{
			if (is_array($interfaces))
			{
				$interfaces = [$interfaces];
			}
			$key .= '.' . implode('.', $interfaces);
		}
		if (!isset(self::$i[$key]))
		{

		}
		return self::$i[$key];
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
			$interfaces = [$interfaces];
		}
		foreach ($interfaces as $interface)
		{
			$info = new ReflectionClass($object);
			if ($info->implementsInterface($interface))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Get class name from configuration
	 * @param type $config
	 * @return string
	 */
	private function _getClassName($config)
	{
		if (is_string($config))
		{
			return new $config;
		}
		return $config[$this->di->classField];
	}

	/**
	 * Instantiate object basd on configuration
	 * @param string|array $config
	 * @return object
	 */
	private function _instantiate($config)
	{
		if (is_string($config))
		{
			return new $config;
		}
		return $this->di->apply($config);
	}

}
