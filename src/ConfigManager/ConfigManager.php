<?php
namespace ConfigManager;

use ConfigManager\Exception\NoLoaderException;

class ConfigManager
{
	const LOADERS = array(
		"json" => __NAMESPACE__ ."\\JsonConfigLoader",
	);

	public $config = array();

	protected $mainFilePath;

	public function __construct($path, $exceptionOnNotFound = true)
	{
	    $this->mainFilePath = $path;
		$this->config = $this->loadConfig($path, $exceptionOnNotFound);
	}

	protected function loadConfig($path, $exceptionOnNotFound)
    {
        if (is_array($path))
            return $path;
        else if (is_object($path))
            return $this->castObjectToArray($path);

        $loader = $this->getLoader($path);

        return $loader->load($path, $exceptionOnNotFound);
    }

	protected function getLoader($path)
	{
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		if (!array_key_exists($ext, self::LOADERS))
			throw new NoLoaderException("No loader available for '.$ext' config files");

		$className = self::LOADERS[$ext];

		return new $className;
	}

	public function mergeConfig($path, $exceptionOnNotFound = true)
    {
        $newConfig = $this->loadConfig($path, $exceptionOnNotFound);
        $this->config = array_replace_recursive($this->config, $newConfig);
    }

    public function set(string $path, $value): void
    {
        $config = &$this->config;

        while (true)
        {
            [$root, $path] = $this->splitPath($path);

            if ($path)
            {
                if (!isset($config[$root]) || !is_array($config[$root]))
                    $config[$root] = [];

                $config = &$config[$root];
            }
            else
            {
                if ($root === "[]")
                    $config[] = $value;
                else
                    $config[$root] = $value;

                break ;
            }
        }
    }

    public function save(string $path = null): bool
    {
        if (!$path)
            $path = $this->mainFilePath;

        $loader = $this->getLoader($path);

        $success = $loader->save($path, $this->config);

        return $success;
    }

    public function get($keyChain = null, $default = null)
    {
        $value = $this->getAssoc($keyChain, $default);
        $value = $this->castArrayToObject($value);

        return $value;
    }

    public function getAssoc($keyChain = null, $default = null)
    {
        if ($keyChain === null)
            return $this->config;

        $value = $this->internGet($keyChain, $this->config, $default);

        return $value;
    }

	protected function internGet($keyChain, array $data, $default)
	{
		list($root, $rest) = $this->splitPath($keyChain);

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

	protected function splitPath($path)
	{
        $length = mb_strlen($path);
        $root = "";
        $i = 0;
        $prevWasSeparator = false;

        for ($i = 0; $i < $length; ++$i)
        {
            $c = $path[$i];

            if ($c === ".")
            {
                if ($i + 1 < $length && $path[$i + 1] === ".")
                    ++$i;
                else
                    break ;
            }

            $root .= $c;
        }

        $rest = substr($path, $i + 1);

        if (!$rest)
            $rest = null;

        return [$root, $rest];
	}

    protected function castObjectToArray($obj)
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

    protected function castArrayToObject($value)
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

    protected function isAssocArray($array)
    {
        if (!is_array($array) || count($array) === 0)
            return false;

        return (array_keys($array) !== range(0, count($array) - 1));
    }
}
