<?php

class Context
{
    private $Connection;
    private $Migrations;
    private $Path;

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
        $this->Path = $migrations;
    }

    function equalize()
    {
        if (isset($this->Path)) {
            if ($tables = $this->Connection->execute("SHOW TABLES")->fetch_all(MYSQLI_ASSOC)) {
                $repositories = [];
                if (sizeof($tables) > 0) {
                    foreach ($tables as $data) {
                        $repositories[] = $data['Tables_in_apy'];
                        if (isset($data['Tables_in_apy']) && !array_key_exists($data['Tables_in_apy'], $this->Migrations))
                            $this->Repository($data['Tables_in_apy'])->__save($this->Path);
                    }
                }
                foreach (array_keys($this->Migrations) as $repository) {
                    if (array_search($repository, $repositories, true) != "")
                        $this->Repository($repository)->__refresh();
                    else
                        $this->Repository($repository)->__instal();
                }
            }
            return (["Code" => 200, "Message" => "Banco de dados equalizado com sucesso"]);
        }
    }

    function Repository($name)
    {
        $options = $this->Migrations[$name] ?? [];
        $options['connection'] = $this->Connection;
        $options['table'] = $name;

        return new Repository($options);
    }
}
