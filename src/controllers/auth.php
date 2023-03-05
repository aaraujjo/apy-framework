<?php

require_once("./libs/APY/index.php");

class Auth extends Controller
{
    function __construct($container)
    {
        parent::__construct($container);
    }

    public function login($email, $password): POST
    {
        return Response::POST(200, ($this->AuthGuard)->createToken(1));
    }

}