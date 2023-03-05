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

    static function PUT($code = 200, $message = "") {
        return new PUT($code, $message);
    }

    static function DELETE($code = 200, $message = "") {
        return new DELETE($code, $message);
    }
}