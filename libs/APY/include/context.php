<?php

class Context
{
    private $Connection;
    private $Map;

    function __construct($connection, $map)
    {
        if (is_dir($map)) {
            $Map = [];
            foreach (glob($map . "/*.json") as $filename) {
                if ($options = json_decode(file_get_contents($filename), true))
                    $Map = array_merge($Map, $options);
            }
            $this->Map = $Map;
        } else if (str_contains($map, ".json") && file_exists($map))
            $this->Map = json_decode(file_get_contents($map), true);
        else
            die("Context Options::Map does not exists");

        $this->Connection = new Connection($connection);
        $this->load();
    }

    function load($override = true)
    {
        $Loaded = [];
        foreach (array_keys($this->Map) as $Name) {
            $Loaded[$Name] = $Name . " " . ($this->Repository($Name)->load($override) ? "was loaded with sucessfull" : "cannot load");
        }
        return $Loaded;
    }

    function Repository($name)
    {
        $options = $this->Map[$name] ?? null;
        if (isset($options)) {
            $options['connection'] = $this->Connection;
            $options['name'] = ucfirst($name);
            return new Repository($options);
        }
        return false;
    }
}
