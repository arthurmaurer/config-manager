<?php
namespace Framework\Config;

class JsonConfigLoader extends ConfigLoader
{
	public function parse($content)
	{
		return json_decode($content, false);
	}
}
