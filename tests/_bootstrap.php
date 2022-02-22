<?php
// This is global bootstrap for autoloading
use Maslosoft\Gazebo\Gazebo;

date_default_timezone_set('Europe/Paris');

error_reporting(E_ALL);

echo "Gazebo " . (new Gazebo)->getVersion() . PHP_EOL;