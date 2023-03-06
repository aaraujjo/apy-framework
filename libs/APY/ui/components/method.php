<?php

class MethodComponent
{
    private $Type;
    private $Name;
    private $Params;
    private $Parent;

    function __construct($parent, $name, $type, $parameters)
    {
        $Params = [];
        foreach ($parameters as $parameter)
            $Params[] = '<input type="text" class="input" name="' . $parameter->getName() . '" placeHolder="' . $parameter->getName() . '">';

        $this->Params = implode("", $Params);
        $this->Parent = $parent;
        $this->Name = $name;
        $this->Type = $type;
    }

    function render()
    {
        $route = strtolower($this->Parent . "/" . $this->Name);
        return <<<HTML
            <div>
                <form method="post">
                    <div class="type">{$this->Type}</div>
                    <label class="method">{$route}</label>
                    {$this->Params}
                    <input type="hidden" name="method" value=$this->Name>
                    <input type="hidden" name="controller" value=$this->Parent>
                    <input type="submit" name="ui" value="Send" class="button" style="height: 28px; font-size: 14px;">
                </form>
            </div>
        HTML;
    }
}