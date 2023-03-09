<?php

require_once("ui/index.php");
foreach (glob("./libs/APY/include/*.php") as $filename)
    require_once($filename);

class APY
{
    private static $Container;
    private static $Response;
    private static $Request;
    private static $UI;

    static function configure($options, $process = false)
    {
        try {
            // Instance Context
            self::$Container['context'] = new Context($options['connection'], $options['migrations'] ?? null);

            // Instance AuthGuard
            self::$Container['guard'] = new AuthGuard($options['authguard']);

            // Instance Response
            self::$Response = new Response(200, "Welcome to APY Framework");

            // Auto Include Controllers
            if (isset($options['controllers']) && is_dir($options['controllers'])) {
                foreach (glob($options['controllers'] . "/*.php") as $filename)
                    require_once($filename);
            }

            // Instance UI
            if (isset($options['ui']) && $options['ui'] == "true")
                self::$UI = new RestUI(["controllers" => self::controllers()]);

            // Instance Request
            self::$Request = new Request();

            // Process Request
            if ($process) {
                APY::process(true);
            }
        } catch (Exception $err) {
            die($err->getMessage());
        }
    }

    static function process($echo = false)
    {
        if (!empty(self::$Request)) {
            if (isset(self::$Request->Args['ui']) && self::$Request->Args['ui'] == 'Equalize Database') {
                self::$Response = (self::$Container['context'])->equalize();
            } else {
                try {
                    if ($Controller = self::$Request->Controller) {
                        if ($Controller = new $Controller(self::$Container))
                            self::$Response = $Controller->call(self::$Request);
                    }
                } catch (Exception $err) {
                    die($err->getMessage());
                }
            }
        }
        self::response($echo);
    }

    static function response($echo = false)
    {
        self::$Response = (is_subclass_of(self::$Response, "Response")) ? self::$Response : (object)self::$Response;

        http_response_code(self::$Response->Code);
        $Response = self::$Response->Message;

        if (isset(self::$UI) && (isset($_REQUEST['ui']) || empty(self::$Request->Controller)))
            self::$Response = self::$UI->state(["debugger" => $Response]);

        if ($echo)
            echo self::$Response;

        return $Response;
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
}
