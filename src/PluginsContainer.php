<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Gazebo;

use ArrayAccess;
use Countable;
use Iterator;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Gazebo\Exceptions\GazeboException;

/**
 * PluginsContainer
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PluginsContainer implements ArrayAccess, Countable, Iterator
{

	/**
	 * Values holder
	 * @var array[]
	 */
	private array $_values = [];

	/**
	 * DI container
	 * @var EmbeDi
	 */
	private EmbeDi $_di;

	/**
	 * Create instance with optional configuration.
	 * This also prevents setting bogus configuration keys.
	 * This will unset all public properties and pass them thru get/set and allow array access.
	 * @param mixed[] $config
	 */
	public function __construct($config = [])
	{
		$this->_di = new EmbeDi();
		$this->apply($config);
	}

	/**
	 * Apply configuration
	 * @param mixed[] $config
	 */
	public function apply($config)
	{
		foreach ((array)$config as $name => $value)
		{
			$this->offsetSet($name, $value);
		}
	}

	public function toArray()
	{
		return $this->_values;
	}

	public function has($name)
	{
		return ClassChecker::exists($name);
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
			throw new GazeboException("Class `$name` used as key does not exists. Tried to get value.");
		}
		return $this->_values[$name];
	}

	public function offsetSet($name, $value)
	{
		if (!$this->has($name))
		{
			throw new GazeboException("Class `$name` used as key does not exists. Tried to set value.");
		}
		foreach($value as $cfg)
		{
			if(is_array($cfg))
			{
				$className = $cfg[$this->_di->classField];
			}
			else
			{
				$className = $cfg;
			}
			if (!$this->has($className))
			{
				throw new GazeboException("Class `$className` used as value does not exists. Tried to set value.");
			}
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
