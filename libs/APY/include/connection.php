<?php

class Connection
{
    private $Name;
    private $Database;

    function __construct($env)
    {
        $this->Database = new mysqli($env['host'], $env['user'], $env['key'], $env['name']);
        $this->Name = $env['name'];
    }

    // function table_exists($table)
    // {
    //     $result = $this->execute("SELECT * FROM information_schema.tables WHERE table_schema = '" . $this->Name . "' AND table_name = '" . $table . "' LIMIT 1;");
    //     if (sizeof($result) > 0)
    //         return true;

    //     return false;
    // }

    function execute($query, $values = [])
    {
        if ((sizeof(explode(";", $query)) - 1) > 1) {
            if ($Statement = $this->Database->multi_query($query)) {
                do {
                } while ($this->Database->next_result());
                return true;
            }
        } else {
            $Statement = $this->Database->prepare($query);
            if ($Statement && $Statement->execute($values))
                return $Statement->get_result();
        }
        return null;
    }
}
