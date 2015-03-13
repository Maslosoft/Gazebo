<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Gazebo;

use ArrayAccess;
use Maslosoft\Gazebo\Exceptions\GazeboException;
use ReflectionObject;
use ReflectionProperty;

/**
 * Plugin container for easier managing complex array
 * and to allow some php docs on otherways hardly documented arrays.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
abstract class PluginContainer implements ArrayAccess
{

	/**
	 * Values holder
	 * @var mixed[]
	 */
	private $_values = [];

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

// <editor-fold defaultstate="collapsed" desc="__get/__set implementation">
	
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

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="ArrayAccess implementation">

	public function offsetExists($name)
	{
		return array_key_exists($name, $this->_values);
	}

	public function offsetGet($name)
	{
		if (!$this->offsetExists($name))
		{
			throw new GazeboException("Configuration property `$name` does not exists. Tried to get value.");
		}
		return $this->_values[$name];
	}

	public function offsetSet($name, $value)
	{
		if (!$this->offsetExists($name))
		{
			throw new GazeboException("Configuration property `$name` does not exists. Tried to set value.");
		}
		return $this->_values[$name] = $value;
	}

	public function offsetUnset($name)
	{
		if (!$this->offsetExists($name))
		{
			throw new GazeboException("Configuration property `$name` does not exists. Tried to unset value.");
		}
		return $this->_values[$name] = $value;
	}

// </editor-fold>
}
