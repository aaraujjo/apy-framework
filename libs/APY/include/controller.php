<?php

class Controller
{
    public $Token;
    public $Context;
    public $AuthGuard;

    function __construct($args)
    {
        $this->Token = $args['token'] ?? null;
        $this->Context = $args['context'] ?? null;
        $this->AuthGuard = $args['guard'] ?? null;
    }

    function call($name, $arguments)
    {
        if ($method = new ReflectionMethod($this, $name)) {
            if ($method->getReturnType() != $_SERVER['REQUEST_METHOD'])
                die("Método diferente do retorno");

            if ($method->isProtected() && !($this->AuthGuard)->isValidToken($this->Token))
                die("Não autorizado");

            $args = [];
            foreach ($method->getParameters() as $parameter) {
                if (!isset($arguments[$parameter->getName()]) && !$parameter->isOptional())
                    die($parameter->getName() . " Parameter necessary");

                $args[$parameter->getName()] = $arguments[$parameter->getName()];
            }
            return $this->$name(...$args);
        }
        die("cannot execute method");
    }

    function methods()
    {
        $diff = array("__construct", "call", "methods");
        $methods = [];

        foreach (array_diff(get_class_methods($this), $diff) as $name) {
            $reflection = new ReflectionMethod($this, $name);
            $methods[$name] = [
                "type" =>  ($reflection->getReturnType()->getName()),
                "parameters" => array_diff($reflection->getParameters(), $diff)
            ];
        }
        return $methods;
    }
}
