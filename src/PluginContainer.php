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

use ArrayAccess;
use Countable;
use Iterator;
use Maslosoft\Gazebo\Exceptions\GazeboException;
use ReflectionObject;
use ReflectionProperty;

/**
 * Plugin container for easier managing complex array
 * and to allow some php docs on otherways hardly documented arrays.
 * This restricts array like access to only public defined properties.
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
abstract class PluginContainer implements ArrayAccess, Countable, Iterator
{

	/**
	 * Values holder
	 * @var mixed[]
	 */
	private $_values = [];

	/**
	 * Property accessed fields
	 * @var bool[]
	 */
	private $_properties = [];

	/**
	 * Create instance with optional configuration.
	 * This also prevents setting bogus configuration keys.
	 * This will unset all public properties and pass them thru get/set and allow array access.
	 * @param mixed[] $config
	 */
	public function __construct($config = [])
	{
		$info = new ReflectionObject($this);
		foreach ($info->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
		{
			/* @var $property ReflectionProperty */
			if ($property->isStatic())
			{
				continue;
			}
			$this->_properties[$property->name] = true;
			unset($this->{$property->name});
		}
		$this->apply($config);
	}

	/**
	 * Apply configuration
	 * @param mixed[] $config
	 */
	public function apply($config)
	{
		foreach ($config as $name => $value)
		{
			$this->offsetSet($name, $value);
		}
	}

	public function has($name)
	{
		return array_key_exists($name, $this->_properties);
	}

// <editor-fold defaultstate="collapsed" desc="__* magic implementation">

	/**
	 * This will be called instead of public getting properties.
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->offsetGet($name);
	}

	/**
	 * This will be called instead of public setting properties.
	 * @param string $name Configuration key value
	 * @param mixed $value Configuration value
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		$this->offsetSet($name, $value);
	}

	/**
	 * Unset
	 * @param string $name
	 */
	public function __unset($name)
	{
		$this->offsetUnset($name);
	}

	/**
	 * Isset
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return $this->offsetExists($name);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="ArrayAccess implementation">

	public function offsetExists($name)
	{
		return array_key_exists($name, $this->_values);
	}

	public function offsetGet($name)
	{
		if (!$this->has($name))
		{
			throw new GazeboException("Configuration property `$name` does not exists. Tried to get value.");
		}
		return $this->_values[$name];
	}

	public function offsetSet($name, $value)
	{
		if (!$this->has($name))
		{
			throw new GazeboException("Configuration property `$name` does not exists. Tried to set value.");
		}
		return $this->_values[$name] = $value;
	}

	public function offsetUnset($name)
	{
		if (!$this->has($name))
		{
			throw new GazeboException("Configuration property `$name` does not exists. Tried to unset value.");
		}
		unset($this->_values[$name]);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Countable implementation">

	public function count($mode = COUNT_NORMAL)
	{
		return count($this->_values, $mode);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Iterator implementation">

	public function current()
	{
		return current($this->_values);
	}

	public function key()
	{
		return key($this->_values);
	}

	public function next()
	{
		return next($this->_values);
	}

	public function rewind()
	{
		return reset($this->_values);
	}

	public function valid()
	{
		return $this->has($this->key()) && $this->offsetExists($this->key());
	}

// </editor-fold>
}
