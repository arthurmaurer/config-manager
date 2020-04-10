<?php
namespace ConfigManager;

abstract class ConfigLoader
{
	abstract public function parse($content);
	abstract public function serialize($data): string;

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

	public function save(string $filePath, $data): bool
    {
        $serializedData = $this->serialize($data);
        $success = file_put_contents($filePath, $serializedData);

        return ($success !== false);
    }
}
