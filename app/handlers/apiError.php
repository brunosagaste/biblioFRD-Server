<?php

namespace App\Handlers;

class apiError extends \Exception {
    public function __invoke($request, $response, $exception)
    {
        $status = /*$exception->getCode() ?:*/ 403;
        $data = [
            "error" => true,
            "status" => 403,
            "message" => $exception->getMessage(),
            "developerMessage" => ""

        ];
        $body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return $response
                   ->withStatus($status)
                   ->withHeader("Content-type", "application/json")
                   ->write($body);
    }
}