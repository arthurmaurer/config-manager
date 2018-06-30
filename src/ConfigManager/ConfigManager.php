<?php
namespace ConfigManager;

class ConfigManager
{
	public $config = array();

	public function __construct($path, $exceptionOnNotFound = true)
	{
		$loader = $this->getLoader($path);
		$this->config = $loader->load($path, $exceptionOnNotFound);
	}

	private function getLoader($path)
	{
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$loaders = $this->getLoaders();

		if (!array_key_exists($ext, $loaders))
			throw new \Exception("No loaders for .$ext config files");

		$className = $loaders[$ext];

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

	protected function getLoaders()
    {
        return array(
            "json" => __NAMESPACE__ ."\\JsonConfigLoader",
        );
    }
}