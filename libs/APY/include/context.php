<?php

class Context
{
    private $Connection;
    private $Migrations;

    function __construct($connection, $migrations)
    {
        if (is_dir($migrations)) {
            $Map = [];
            foreach (glob($migrations . "/*.json") as $filename) {
                if ($options = json_decode(file_get_contents($filename), true))
                    $Map = array_merge($Map, $options);
            }
            $this->Migrations = $Map;
        } else if (str_contains($migrations, ".json") && file_exists($migrations))
            $this->Migrations = json_decode(file_get_contents($migrations), true);
        else
            die("Context Options::Map does not exists");

        $this->Connection = new Connection($connection);
        $this->load();
    }

    function load($override = true)
    {
        $Loaded = [];
        foreach (array_keys($this->Migrations) as $Name) {
            $Loaded[$Name] = $Name . " " . ($this->Repository($Name)->load($override) ? "was loaded with sucessfull" : "cannot load");
        }
        return $Loaded;
    }

    function Repository($name)
    {
        $options = $this->Migrations[$name] ?? null;
        if (isset($options)) {
            $options['connection'] = $this->Connection;
            $options['name'] = ucfirst($name);
            return new Repository($options);
        }
        return false;
    }
}
