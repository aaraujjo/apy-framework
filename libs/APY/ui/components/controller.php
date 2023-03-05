<?php

require_once("method.php");

class ControllerComponent
{
    private $Controller;
    private $Methods;

    function __construct($controller, $methods)
    {
        $Methods = [];
        foreach ($methods as $method => $parameters) {
            $Methods[] = (new MethodComponent($method, $parameters['type'], $parameters['parameters']))->render();
        }

        $this->Methods = implode("", $Methods);
        $this->Controller = $controller;
    }

    function render()
    {
        return <<<HTML
            <h2>{$this->Controller}</h2>
            <div class="methods">
                {$this->Methods}
            </div>
        HTML;
    }
}
