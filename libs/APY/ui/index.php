<?php

foreach (glob("./libs/APY/ui/components/*.php") as $filename)
    require_once($filename);

class RestUI
{
    private $Style;
    private $Debugger;
    private $Controllers;

    function __construct($controllers = [], $debugger = null)
    {
        $Controllers = [];
        foreach ($controllers as $controller => $methods)
            $Controllers[] = (new ControllerComponent($controller, $methods))->render();

        $this->Style = file_get_contents("./libs/APY/ui/style/style.css");
        $this->Controllers = implode("", $Controllers);
    }

    function render()
    {
        return <<<HTML
            <html>
                <head>
                    <title>APY</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>{$this->Style}</style> 
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
                                    {$this->Controllers}
                                </div>
                            </div>
                            <!-- <div class="pannel">
                                <h1>Repositories</h1>
                            </div> -->
                        </div>
                        <div class="debugger">
                            <h1>Debugger</h1>
                        </div>
                    </div>
                </body>
            </html>
        HTML;
    }
}
