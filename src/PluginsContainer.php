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
use const COUNT_NORMAL;

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
	public function apply($config): void
	{
		foreach ((array)$config as $name => $value)
		{
			$this->offsetSet($name, $value);
		}
	}

	public function toArray(): array
	{
		return $this->_values;
	}

	public function has($name): bool
	{
		return ClassChecker::exists($name);
	}

// <editor-fold defaultstate="collapsed" desc="__* magic implementation">

	/**
	 * This will be called instead of public getting properties.
	 * @param string $name
	 * @return mixed
	 */
	public function __get(string $name)
	{
		return $this->offsetGet($name);
	}

	/**
	 * This will be called instead of public setting properties.
	 * @param string $name  Configuration key value
	 * @param mixed  $value Configuration value
	 * @throws GazeboException
	 */
	public function __set(string $name, $value): void
	{
		$this->offsetSet($name, $value);
	}

	/**
	 * Unset
	 * @param string $name
	 */
	public function __unset(string $name)
	{
		$this->offsetUnset($name);
	}

	/**
	 * Isset
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name): bool
	{
		return $this->offsetExists($name);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="ArrayAccess implementation">

	public function offsetExists($name): bool
	{
		return array_key_exists($name, $this->_values);
	}

	#[\ReturnTypeWillChange]
	public function offsetGet($name)
	{
		if (!$this->has($name))
		{
			throw new GazeboException("Class `$name` used as key does not exists. Tried to get value.");
		}
		return $this->_values[$name];
	}

	public function offsetSet($name, $value): void
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
		$this->_values[$name] = $value;
	}

	public function offsetUnset($name): void
	{
		if (!$this->has($name))
		{
			throw new GazeboException("Configuration property `$name` does not exists. Tried to unset value.");
		}
		unset($this->_values[$name]);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Countable implementation">

	public function count($mode = COUNT_NORMAL): int
	{
		return count($this->_values, $mode);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Iterator implementation">

	#[\ReturnTypeWillChange]
	public function current()
	{
		return current($this->_values);
	}

	#[\ReturnTypeWillChange]
	public function key()
	{
		return key($this->_values);
	}

	public function next(): void
	{
		next($this->_values);
	}

	public function rewind(): void
	{
		reset($this->_values);
	}

	public function valid(): bool
	{
		return $this->has($this->key()) && $this->offsetExists($this->key());
	}

// </editor-fold>
}
