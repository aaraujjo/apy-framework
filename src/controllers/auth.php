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
        $this->Context->load();
        return Response::POST(400, "E-mail ou senha incorretos.");
        // if ($UserRepository = $this->Context->Repository("User")) {
        //     if ($User = $UserRepository->select(["id"])->where(["email" => $email, "password" => $password])->first()) {
        //         $Token = $this->AuthGuard->createToken($User["id"]);
        //         return Response::POST(200, $Token);
        //     }
        // }
        // return Response::POST(400, "E-mail ou senha incorretos.");
    }
}
