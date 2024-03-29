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

	public const DefaultInstanceId = 'gazebo';

	/**
	 * Version holder
	 * @var string|null
	 */
	private static ?string $_version = null;

	/**
	 * Get current gazebo version.
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		if (null === self::$_version)
		{
			self::$_version = require __DIR__ . '/version.php';
		}
		return self::$_version;
	}

}
