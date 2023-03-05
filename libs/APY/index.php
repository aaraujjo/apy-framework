<?php

require_once("ui/index.php");
foreach (glob("./libs/APY/include/*.php") as $filename)
    require_once($filename);

class APY
{
    private static $Container;
    private static $Response;
    private static $Args;
    private static $UI;

    static function configure($options)
    {
        try {
            // Instance Context
            self::$Container['context'] = new Context($options['connection'], $options['map']);

            // Instance AuthGuard
            self::$Container['guard'] = new AuthGuard($options['authguard']);

            // Instance Response
            self::$Response = new Response(200, "");

            // Store Arguments
            self::$Args = [
                "GET" => $_GET,
                "POST" => $_POST,
            ];

            // Load Controllers
            foreach (glob($options['controllers'] . "/*.php") as $filename)
                require_once($filename);

            // Instance UI
            self::$UI = (new RestUI(self::controllers()));

            // Process Request
            self::request();
        } catch (Exception $err) {
            die($err->getMessage());
        }
    }

    static function request()
    {
        $Request = parse_url($_SERVER['REQUEST_URI']);
        if ($Request['path'] != '/' && $Routes = explode("/", substr($Request['path'], 1))) {
            if (isset($Routes[0]) && $controller = array_shift($Routes)) {
                if (class_exists($controller) && is_subclass_of($controller, "Controller")) {
                    $Method = implode("_", $Routes);
                    try {
                        $Arguments = self::$Args[$_SERVER['REQUEST_METHOD']]; // Passar o args para a responsabilidade do method
                        if ($Controller = new $controller(self::$Container))
                            self::$Response = $Controller->call($Method, $Arguments);

                        return true;
                    } catch (Exception $err) {
                        die($err->getMessage());
                    }
                }
                die("Controller " . ucfirst($controller) . " does not exist");
            }
        }
    }

    static function controllers()
    {
        $Controllers = [];
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, "Controller")) {
                $Controllers[$class] = (new $class(self::$Container))->methods();
            }
        }
        return $Controllers;
    }

    static function response()
    {
        http_response_code(self::$Response->Code);
        return self::$UI->render();
    }

    static function refresh()
    {
    }
}
