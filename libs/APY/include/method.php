<?php

require_once("response.php");

class GET extends Response
{
    function __construct($code, $message)
    {
        parent::__construct($code, $message);
    }
}

class POST extends Response
{
    function __construct($code, $message)
    {
        parent::__construct($code, $message);
    }
}
