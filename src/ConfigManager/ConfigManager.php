<?php
namespace ConfigManager;

class ConfigManager
{
	const LOADERS = array(
		"json" => __NAMESPACE__ ."\\JsonConfigLoader",
	);

	public $config = array();

	public function __construct($path, $exceptionOnNotFound = true)
	{
		$loader = $this->getLoader($path);
		$this->config = $loader->load($path, $exceptionOnNotFound);
	}

	private function getLoader($path)
	{
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		if (!array_key_exists($ext, self::LOADERS))
			throw new \Exception("No loaders for .$ext config files");

		$className = self::LOADERS[$ext];

		return new $className;
	}

	public function get($keyChain, $default = null)
	{
		return $this->internGet($keyChain, $this->config, $default);
	}

	public function internGet($keyChain, array $data, $default)
	{
		list($root, $rest) = $this->splitKeyChain($keyChain);

		if (!isset($data[$root]))
			return $default;

		$value = $data[$root];

		if ($rest)
		{
			if (is_array($value))
				return $this->internGet($rest, $value, $default);

			return $default;
		}

		return $value;
	}

	private function splitKeyChain($keyChain)
	{
		$root = strstr($keyChain, ".", true);

		if (!$root)
			$root = $keyChain;

		$rest = substr($keyChain, strlen($root) + 1);

		return array($root, $rest);
	}
}