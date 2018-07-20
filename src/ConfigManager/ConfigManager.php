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
		$this->config = $this->loadConfig($path, $exceptionOnNotFound);
	}

	private function loadConfig($path, $exceptionOnNotFound)
    {
        if (is_array($path))
            return $this->castArrayToObject($path);
        else if (is_object($path))
            return $path;

        $loader = $this->getLoader($path);
        return $loader->load($path, $exceptionOnNotFound);
    }

	private function getLoader($path)
	{
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		if (!array_key_exists($ext, self::LOADERS))
			throw new \Exception("No loaders for .$ext config files");

		$className = self::LOADERS[$ext];

		return new $className;
	}

	public function mergeConfig($path, $exceptionOnNotFound = true)
    {
        $newConfig = $this->loadConfig($path, $exceptionOnNotFound);
        $newConfig = $this->castObjectToArray($newConfig);
        $config = $this->castObjectToArray($this->config);
        $config = array_replace_recursive($config, $newConfig);

        $this->config = $this->castArrayToObject($config);
    }

    public function get($keyChain = null, $default = null)
    {
        if ($keyChain === null)
            return $this->config;

        return $this->internGet($keyChain, $this->config, $default);
    }

    public function getAssoc($keyChain, $default = null)
    {
        $result = $this->internGet($keyChain, $this->config, $default);
        $result = $this->castObjectToArray($result);

        return $result;
    }

	public function internGet($keyChain, \StdClass $data, $default)
	{
		list($root, $rest) = $this->splitKeyChain($keyChain);

		if (!isset($data->{$root}))
			return $default;

		$value = $data->{$root};

		if ($rest)
		{
			if (is_object($value))
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

    private function castObjectToArray($obj)
    {
        if (is_object($obj))
            $obj = (array)$obj;

        if (is_array($obj))
        {
            $new = array();

            foreach ($obj as $key => $val)
                $new[$key] = $this->castObjectToArray($val);
        }
            else $new = $obj;

        return $new;
    }

    private function castArrayToObject($value)
    {
        if ($this->isAssocArray($value))
        {
            $out = new \StdClass;

            foreach ($value as $key => $val)
                $out->$key = $this->castArrayToObject($val);

            return $out;
        }
        else if (is_array($value))
        {
            $out = array();

            foreach ($value as $key => $val)
                $out[$key] = $this->castArrayToObject($val);

            return $out;
        }
        else
            return $value;
    }

    private function isAssocArray($array)
    {
        if (!is_array($array) || array() === $array)
            return false;

        return (array_keys($array) !== range(0, count($array) - 1));
    }
}
