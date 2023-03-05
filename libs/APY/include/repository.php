<?php

require_once("builder.php");

class Repository extends Builder
{
    private $Primary;
    private $Model;
    private $Data;
    private $Name;

    function __construct($args = [])
    {
        $this->Primary = $args['primary'] ?? null;
        $this->Model = $args['model'] ?? null;
        $this->Name = $args['name'] ?? null;
        $this->Data = $args['data'] ?? [];
        parent::__construct($args);

        $this->Keys = array_keys($args['model']);

        if (!isset($this->Table))
            die("Repository " . $this->Name . "::Table cannot be empty");
        elseif (!isset($this->Model) || sizeof($this->Model) < 1)
            die("Repository " . $this->Name . "::Model cannot be empty");
    }

    function load($override = false)
    {
        if ($override)
            $this->Query[] = "DROP TABLE IF EXISTS `" . $this->Table . "`;";

        $this->Query[] = "CREATE TABLE IF NOT EXISTS `" . $this->Table . "` (";

        $Tables = [];
        foreach ($this->Model as $Prop => $Params) {
            $Table = [];
            $Table[] = "`" . $Prop . "`";
            $Table[] = $Params['type'] ?? 'int';
            $Table[] = "(" . ($Params['lenght'] ?? 11) . ")";
            $Table[] = (isset($Params['type']) && $Params['type'] == 'varchar') ? "COLLATE utf8_unicode_ci" : null;
            $Table[] = (!array_key_exists("default", $Params)) ? "NOT NULL" : "DEFAULT" . (($Params['default'] != null && strtolower($Params['default']) != "null") ? (" = " . $Params['default']) : " NULL");

            $Tables[] = implode(" ", $Table);
        }

        $this->Query[] = implode(",", $Tables);
        $this->Query[] = ") ENGINE=InnoDB DEFAULT CHARACTER SET = utf8;";
        try {

            if ($this->result()) {
                if (sizeof($this->Data) > 0)
                    $this->insert($this->Data)->result();

                return true;
            }
        } catch (Exception $err) {
            die($err->getMessage());
        }
        return false;
    }

    function unload()
    {
        $this->Query[] = "DROP TABLE IF EXISTS " . $this->Table . ";";
        return parent::result();
    }

    function parse($values, $nullable = false)
    {
        $Values = [];
        if (isset($values) && sizeof($values) > 0) {
            foreach ($this->Model as $Key => $Options) {
                $Value = $values[$Key] ?? null;
                if (!isset($Value)) {
                    if (!isset($Options['default']) && ($this->Primary != $Key))
                        die("Repository " . $this->Name . "::Model[" . $Key . "] is not nullable");

                    if ($nullable)
                        $Value = "NULL";
                }
                $Values[$Key] = $Value;
            }
        }
        return $Values;
    }

    function select($fields = [])
    {
        if (sizeof($fields) < 1)
            $fields = $this->Keys ?? "*";

        return parent::select($fields);
    }

    function insert($values = [])
    {
        if (isset(array_keys($values)[0])) {
            $Values = [];
            foreach ($values as $value)
                $Values[] = $this->parse($value);
        } else
            $Values = $this->parse($values);

        return parent::insert($Values);
    }

    function update($values = [])
    {
        return parent::update($values);
    }
}
