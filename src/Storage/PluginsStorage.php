<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Gazebo\Storage;

use Maslosoft\EmbeDi\StaticStorage;

/**
 * PluginsStorage
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PluginsStorage extends StaticStorage
{

	/**
	 * Plugins instances
	 * @var object[][]
	 */
	public $plugins = [];

}
