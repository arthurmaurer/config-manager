<?php
namespace ConfigManager;

class JsonConfigLoader extends ConfigLoader
{
	public function parse($content)
	{
		return json_decode($content, true);
	}
}
