<?php
namespace ConfigManager;

class JsonConfigLoader extends ConfigLoader
{
	public function parse($content)
	{
		return json_decode($content, true);
	}

	public function serialize($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
