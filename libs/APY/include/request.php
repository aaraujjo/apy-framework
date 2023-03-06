<?php

class Request
{
    public $Controller;
    public $Method;
    public $Args;

    function __construct()
    {
        $this->Args = $_REQUEST ?? [];
        $this->Method = $this->Args['method'] ?? null;
        $this->Controller = $this->Args['controller'] ?? null;

        if (empty($this->Controller)) {
            $Base = strtolower(basename(dirname(__FILE__)));
            $Path = str_replace("/" . $Base, "", parse_url($_SERVER['REQUEST_URI']));
            $Path = str_replace("apy", "", $Path);
            if ($Path['path'] != '/' && $Routes = explode("/", substr($Path['path'], 1))) {
                $this->Controller = array_shift($Routes);
                $this->Method = implode("_", $Routes);
            }
        }

        if (!isset($this->Controller))
            return null;
    }
}
