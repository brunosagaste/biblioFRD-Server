<?php

use Slim\App;

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);
    $app->add(new Slim\Middleware\JwtAuthentication([
    "path" => ["/api", "/copy", "/hold", "/renewal", "/regid", "/password"],
    "secure" => false,
    "attribute" => "decoded_token_data",
    "secret" => "supersecretkeyyoushouldnotcommittogithub",
    "algorithm" => ["HS256"],
    "error" => function ($req, $res, $args) {
        $data["status"] = "error";
        $data["message"] = $args["message"];
        return $res
            ->withHeader("Content-Type", "application/json")
            ->withStatus(401)
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]));

    $checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
    $trustedProxies = ['10.0.0.1', '10.0.0.2']; // Note: Never trust the IP address for security processes!
    $app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));

};

