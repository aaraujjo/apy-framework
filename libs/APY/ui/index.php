<?php

foreach (glob("./libs/APY/ui/components/*.php") as $filename)
    require_once($filename);

class RestUI
{
    private $Controllers = null;
    private $Debugger = null;

    function __construct($state = [])
    {
        $this->state($state);
    }

    function state($values = [])
    {
        $this->Controllers = $values['controllers'] ?? $this->Controllers;
        $this->Debugger = $values['debugger'] ?? $this->Debugger;
        return $this->render();
    }

    function controllers()
    {
        $Controllers = [];
        foreach ($this->Controllers as $controller => $methods)
            $Controllers[] = (new ControllerComponent($controller, $methods))->render();

        return implode("", $Controllers);
    }

    function render()
    {
        $style = file_get_contents("./libs/APY/ui/style/style.css");

        return <<<HTML
            <html>
                <head>
                    <title>APY</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>{$style}</style> 
                </head>
                <body>
                    <div class="header">
                        <div class="logo">
                            <img src="./libs/APY/ui/style/logo.png" alt="logo">
                            <a href="#" class="">REST UI</a>
                        </div>
                        <form method="post" class="navForm">
                            <input type="submit" value="Refresh Database" class="button">
                        </form>
                    </div>
                    <div class="body">
                        <div class="pannels">
                            <div class="pannel">
                                <h1>Controllers</h1>
                                <div class="controllers">
                                    {$this->controllers()}
                                </div>
                            </div>
                            <!-- <div class="pannel">
                                <h1>Repositories</h1>
                            </div> -->
                        </div>
                        <div class="debugger">
                            <h1>Debugger</h1>
                            <textarea>{$this->Debugger}</textarea>
                        </div>
                    </div>
                </body>
            </html>
        HTML;
    }
}
