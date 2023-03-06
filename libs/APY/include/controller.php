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

    function call($request)
    {
        $name = $request->Method;
        if (method_exists($this, $name)) {
            if ($method = new ReflectionMethod($this, $name)) {
                if ($method->getReturnType() != $_SERVER['REQUEST_METHOD'])
                    return (["Code" => "400", "Message" => "Método diferente"]);

                if ($method->isProtected() && !($this->AuthGuard)->isValidToken($this->Token))
                    return (["Code" => "403", "Message" => "Não autenticado"]);

                $args = [];
                foreach ($method->getParameters() as $parameter) {
                    if (!isset($request->Args[$parameter->getName()]) && !$parameter->isOptional())
                        return (["Code" => "403", "Message" => "Parameter necessary"]);

                    $args[$parameter->getName()] = $request->Args[$parameter->getName()];
                }
                return $this->$name(...$args);
            }
        }
        return (["Code" => "403", "Message" => "cannot execute method"]);
    }

    function methods()
    {
        $diff = array("__construct", "call", "methods");
        $methods = [];

        foreach (array_diff(get_class_methods($this), $diff) as $name) {
            $reflection = new ReflectionMethod($this, $name);
            $methods[$name] = [
                "type" => ($reflection->getReturnType()->getName()),
                "parameters" => array_diff($reflection->getParameters(), $diff)
            ];
        }
        return $methods;
    }
}
