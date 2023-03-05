<?php

require_once("./libs/APY/index.php");

$options = [
    "authguard" => ["secret" => "#@APY*&235@!#$", "algorithm" => "AES-128-CBC", "hash" => "SHA256"],
    "connection" => ["host" => "localhost", "user" => "root", "key" => "", "name" => "apy"],
    "controllers" => "./src/controllers",
    "map" => "./src/maps"
];

APY::configure($options);

echo APY::response();