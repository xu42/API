<?php

require_once 'vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Middleware\HttpBasicAuthentication;
use Slim\Middleware\JwtAuthentication;
use Lcobucci\JWT\Builder;


$app = new \Slim\App;


$app->add(new JwtAuthentication([
    "secret" => "cn.xu42.api",
    "rules" => [
        new JwtAuthentication\RequestPathRule([
            "path" => '/',
            "passthrough" => ["/token"]
        ])
    ],
    "callback" => function(ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
        $app->jwt = $arguments["decoded"];
    }
]));


$app->add(new HttpBasicAuthentication([
    "path" => "/token",
    "users" => [
        "user0" => "user0password"
    ]
]));


$app->get("/token", function(ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!$request->hasHeader('key')){
        return $response->withStatus(401);
    }
    $access_token = (new Builder())->setIssuer('https://api.xu42.cn') // Configures the issuer (iss claim)
        ->setAudience('https://api.xu42.cn') // Configures the audience (aud claim)
        ->setId($request->getHeaderLine('key'), true) // Configures the id (jti claim), replicating as a header item
        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
        ->setNotBefore(time()+60) // Configures the time that the token can be used (nbf claim)
        ->setExpiration(time()+3600) // Configures the expiration time of the token (exp claim)
        ->set('scope', ['read']) // Configures a new claim, called "scope"
        ->sign(new \Lcobucci\JWT\Signer\Hmac\Sha256(), 'cn.xu42.api') // ALGORITHM HS256
        ->getToken(); // Retrieves the generated token
    $response = $response->withStatus(200);
    $response = $response->withHeader('Content-type', 'application/json');
    $response->getBody()->write(json_encode(['access_token' => (string) $access_token, 'token_type' => 'bearer', 'expires_in' => $access_token->getClaim('exp') - $access_token->getClaim('iat')]));
    return $response;
});


/**
 * root directory
**/
$app->any('/', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) {
    require_once 'v1/root.php';
    $response->getBody()->write(root::messages());
    return $response;
});


/**
 * v1 cet_score
 * 大学英语四六级成绩查询 V1
*/
$app->get('/v1/cet_score/{name}/{numbers}', function (ServerRequestInterface $request, ResponseInterface $response, $args) use ($app) {
    if(in_array('read', $app->jwt->scope) && $app->jwt->jti == $request->getHeaderLine('key')) {
        require_once 'v1/cet_score/cet_score.php';
        return cet_score::get($request, $response, $args);
    } else {
        return $response->withStatus(401);
    }
});


$app->run();
