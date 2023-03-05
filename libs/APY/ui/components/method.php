<?php

class MethodComponent
{
    private $Type;
    private $Name;
    private $Params;

    function __construct($name, $type, $parameters)
    {
        $Params = [];
        foreach ($parameters as $parameter)
            $Params[] = '<input type="text" class="input" name="' . $parameter->getName() . ' placeHolder="' . $parameter->getName() . '">';

        $this->Params = implode("", $Params);
        $this->Name = $name;
        $this->Type = $type;
    }

    function render()
    {
        return <<<HTML
            <div>
                <form method="post">
                    <div class="type">{$this->Type}</div>
                    <label class="method">{$this->Name}</label>
                    {$this->Params}
                    <input type="submit" name="request" value="Send" class="button" style="height: 28px; font-size: 14px;">
                    <input type="submit" name="request" value="Query" class="button" style="height: 28px; font-size: 14px;">
                </form>
            </div>
        HTML;
    }
}
