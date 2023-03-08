<?php

class Builder
{
    public $Connection;
    public $Values;
    public $Query;
    public $Table;
    public $Keys;

    function __construct($args = [])
    {
        $this->Connection = $args['connection'] ?? null;
        $this->Table = $args['table'] ?? null;
        $this->Keys = $args['keys'] ?? [];
        $this->Values = [];
        $this->Query = [];
    }

    function table($table)
    {
        $this->Table = $table;
        return $this;
    }

    function select($fields = [])
    {
        $this->Query[] = "SELECT " . ((sizeof($fields) > 0) ? implode(", ", $fields) : "*");
        $this->Query[] = "FROM " . $this->Table;
        return $this;
    }

    function join($table, $on)
    {
        $this->Query[] = "JOIN " . $table;
        $this->Query[] = "ON " . $on;
        return $this;
    }

    function insert($values = [])
    {
        $this->Query[] = "INSERT INTO `" . $this->Table . "` (" . implode(", ", $this->Keys) . ") VALUES";
        if (isset(array_keys($values)[0])) {
            $Insertion = [];
            foreach ($values as $value) {
                $Insertion[] = "(" . implode(", ", array_fill(0, sizeof($value), "?")) . ")";
                $this->Values = array_merge($this->Values, array_values($value));
            }
            $this->Query[] = implode(",", $Insertion) . ";";
        } else {
            $this->Query[] = "(" . implode(", ", array_fill(0, sizeof($values), "?")) . ");";
            $this->Values = array_merge($this->Values, array_values($values));
        }
        return $this;
    }

    function update($values = [])
    {
        $this->Query[] = "UPDATE " . $this->Table;
        foreach (array_keys($values) as $key)
            $this->Query[] = "SET " . $key . " = ?";

        $this->Values = array_merge($this->Values, array_values($values));
        return $this;
    }

    function where($conditions = [])
    {
        if (sizeof($conditions) > 0) {
            foreach (array_keys($conditions) as $condition)
                $this->Query[] = (str_contains($this->raw(), "WHERE") ? "AND" : "WHERE") . " " . $condition . " = ?";

            $this->Values = array_merge($this->Values, array_values($conditions));
        }
        return $this;
    }

    function having($conditions = [])
    {
        if (sizeof($conditions) > 0) {
            foreach (array_keys($conditions) as $condition)
                $this->Query[] = (str_contains($this->raw(), "HAVING") ? "AND" : "HAVING") . " " . $condition . " = '?'";

            $this->Values = array_merge($this->Values, $conditions);
        }
        return $this;
    }

    function group($groups = [])
    {
        if (sizeof($groups) > 0)
            $this->Query[] = "GROUP BY " . implode(", ", $groups);

        return $this;
    }

    function order($orders = [])
    {
        if (sizeof($orders) > 0)
            $this->Query[] = "ORDER BY " . implode(", ", $orders);

        return $this;
    }

    function limit($range = [])
    {
        $this->Query[] = "LIMIT " . implode(", ", $range);
        return $this;
    }

    function raw()
    {
        return implode(" ", $this->Query);
    }

    function result()
    {
        $result = $this->Connection->execute($this->raw(), $this->Values);
        $this->Query = [];
        return $result;
    }

    function list()
    {
        if ($list = $this->result()->fetch_all(MYSQLI_ASSOC)) {
            if (sizeof($list) > 0)
                return $list;
        }
        return [];
    }

    function first()
    {
        if (!str_contains($this->raw(), "LIMIT"))
            $this->limit([1]);

        if ($list = $this->list()) {
            if (isset($list[0]))
                return $list[0];
        }

        return null;
    }
}
