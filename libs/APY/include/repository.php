<?php

require_once("builder.php");
require_once("const.php");

class Repository extends Builder
{
    private $Primary;
    private $Model;
    private $Data;
    private $Name;

    function __construct($args = [])
    {
        parent::__construct($args);
        if (!isset($this->Table))
            die("Repository Table::" . $this->Table . " cannot be empty");

        if (!isset($args['model'])) {
            if ($table = $this->Connection->execute("DESCRIBE `" . $this->Table . "`")->fetch_all(MYSQLI_ASSOC)) {
                if (sizeof($table) > 0) {
                    $args['model'] = [];

                    foreach ($table as $column) {
                        $args['model'][$column['Field']] = ['type' => $column['Type']];

                        if (($column['Null'] != 'NO')) {
                            $args[$column['Field']]['default'] = strval($column['Default']);
                        }

                        if (isset($column['Extra']))
                            $args[$column['Field']]['extra'] = $column['Extra'];

                        if ($column['Key'] == 'PRI')
                            $args['primary'] = $column['Field'];
                    }
                }
            }
        }

        if (!isset($args['model']) || sizeof($args['model']) < 1)
            die("Repository " . ucfirst($this->Table) . "::Model cannot be empty");

        $this->Keys = array_keys($args['model']) ?? [];
        $this->Primary = $args['primary'] ?? null;
        $this->Model = $args['model'] ?? [];
        $this->Data = $args['data'] ?? [];
    }


    function __instal()
    {
        $this->Query[] = "DROP TABLE IF EXISTS `" . $this->Table . "`;";
        $this->Query[] = "CREATE TABLE IF NOT EXISTS `" . $this->Table . "` (";

        $this->Query[] = implode(",", $this->__columns());
        $this->Query[] = ") ENGINE=InnoDB DEFAULT CHARACTER SET = utf8;";

        if (isset($this->Primary))
            $this->Query[] = "ALTER TABLE `" . $this->Table . "` ADD PRIMARY KEY (`" . $this->Primary . "`)";

        try {
            $this->result();
            if (sizeof($this->Data) > 0)
                $this->insert($this->Data)->result();

            return true;
        } catch (Exception $err) {
            die($err->getMessage());
        }
        return false;
    }

    function __uninstall()
    {
        $this->Query[] = "DROP TABLE IF EXISTS " . $this->Table . ";";
        return $this->result();
    }

    function __columns()
    {
        $Columns = [];
        foreach ($this->Model as $Prop => $Params) {
            $Column = [];
            $Column[] = "`" . $Prop . "`";
            if (!isset($Params['type']))
                die("falta type");
            else {
                $Column[] = $Params['type'];

                preg_match("/\(.*\)/", $Params['type'], $testando);
                if (!isset($testando[0]))
                    $Column[] = "(" . DefaultLenght[$Params['type']] . ")" ?? null;

                $Column[] = DefaultCollation[$Params['type']] ?? null;
                $Column[] = (!array_key_exists("default", $Params)) ? "NOT NULL" : "DEFAULT " . ((strtolower(strval($Params['default'])) != "") ? (" = " . $Params['default']) : " NULL");
                $Columns[] = implode(" ", $Column);
            }
        }
        return $Columns;
    }

    function __refresh()
    {
        $this->Query[] = "ALTER TABLE `" . $this->Table . "`";

        $Modifies = [];
        foreach ($this->__columns() as $column)
            $Modifies[] = "MODIFY " . $column;

        $this->Query[] = implode(",", $Modifies) . ";";

        try {
            $this->result();
            return true;
        } catch (Exception $err) {
            die($err->getMessage());
        }
        return false;
    }

    function __save($path)
    {
        $MigrationFile = fopen($path . "/" . $this->Table . ".json", "w") or die("Unable to open file!");

        $RepositoryJson = [];
        $RepositoryJson[$this->Table] = [
            "primary" => $this->Primary,
            "model" => $this->Model
        ];

        fwrite($MigrationFile, json_encode($RepositoryJson, JSON_PRETTY_PRINT));
        fclose($MigrationFile);
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
