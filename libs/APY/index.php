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

    static function configure($options, $process = true)
    {
        try {
            // Instance Context
            self::$Container['context'] = new Context($options['connection'], $options['migrations']);

            // Instance AuthGuard
            self::$Container['guard'] = new AuthGuard($options['authguard']);

            // Instance Response
            self::$Response = new Response(200, "Welcome to APY Framework");

            // Load Controllers
            foreach (glob($options['controllers'] . "/*.php") as $filename)
                require_once($filename);

            // Instance UI
            self::$UI = new RestUI(["controllers" => self::controllers()]);

            // Instance Request
            self::$Request = new Request();

            // Process Request
            if ($process) {
                APY::process();
            }
        } catch (Exception $err) {
            die($err->getMessage());
        }
    }

    static function process()
    {
        if (!empty(self::$Request)) {
            try {
                if ($Controller = self::$Request->Controller) {
                    if ($Controller = new $Controller(self::$Container))
                        self::$Response = $Controller->call(self::$Request);
                }
            } catch (Exception $err) {
                die($err->getMessage());
            }
        }
        self::response();
    }

    static function response()
    {
        self::$Response = (is_subclass_of(self::$Response, "Response")) ? self::$Response : (object)self::$Response;

        http_response_code(self::$Response->Code);
        $Response = self::$Response->Message;

        if (isset($_REQUEST['ui']) || empty(self::$Request->Controller))
            $Response = self::$UI->state(["debugger" => $Response]);

        echo $Response;
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
