<?php

require_once("method.php");

class Response
{
    public $Code;
    public $Message;

    function __construct($code, $message)
    {
        $this->Code = $code;
        $this->Message = $message;
    }

    static function GET($code = 200, $message = "") {
        return new GET($code, $message);
    }

    static function POST($code = 200, $message = "") {
        return new POST($code, $message);
    }
}