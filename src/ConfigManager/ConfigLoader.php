<?php
namespace ConfigManager;

abstract class ConfigLoader
{
	public abstract function parse($content);

	public function load($path, $exceptionOnNotFound)
	{
		$content = $this->loadFile($path, $exceptionOnNotFound);
		$config = ($content !== false)
			? $this->parse($content)
			: array();

		if ($config === null)
			throw new \Exception("Could not parse config file $path");

		return $config;
	}

	public function loadFile($path, $exceptionOnNotFound)
	{
		if (!file_exists($path))
		{
			if ($exceptionOnNotFound)
				throw new \Exception("Config file $path doesn't exist");

			return false;
		}

		$content = file_get_contents($path);

		if ($content === false && $exceptionOnNotFound)
			throw new \Exception("Could not read config file $path");

		return $content;
	}
}