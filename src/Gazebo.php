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

/**
 * Rather poor class
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Gazebo
{

	const DefaultInstanceId = 'gazebo';

	/**
	 * Version holder
	 * @var string
	 */
	private static $_version = null;

	/**
	 * Get current addendum version.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		if (null === self::$_version)
		{
			self::$_version = require __DIR__ . '/version.php';
		}
		return self::$_version;
	}

}
