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
            "passthrough" => ["/v1/token"]
        ])
    ],
    "callback" => function(ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
        $app->jwt = $arguments["decoded"];
    },
    "error" => function(ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
        $response->getBody()->write(json_encode(['error' => 'The wrong access_token']));
        $response = $response->withStatus(401);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
]));


$app->add(new HttpBasicAuthentication([
    "path" => "/v1/token",
    "users" => [
        "user0" => "user0password"
    ],
    "error" => function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
        $response->getBody()->write(json_encode(['error' => 'The wrong username and password']));
        $response = $response->withStatus(401);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
]));


/**
 * root directory
 **/
$app->any('/', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) {
    require_once 'v1/root.php';
    $response->getBody()->write(root::messages());
    return $response;
});


/**
 * 获取授权token V1
 * ===============================================
 * GET                   /v1/token
 *
 * BASIC AUTH
 *      Username        {user0}
 *      Password        {user0password}
 *
 * HEADERS
 *      key             {key}
 * ===============================================
 * {user0}              申请到的用于BASIC AUTH获取access_token的username
 * {user0password}      申请到的用于BASIC AUTH获取access_token的password
 * {key}                自定义的key
 */
$app->get("/v1/token", function(ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!$request->hasHeader('key')){
        $response->getBody()->write(json_encode(['error' => 'no key']));
        $response = $response->withStatus(400);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
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
    $response->getBody()->write(json_encode(['access_token' => (string) $access_token, 'token_type' => 'Bearer', 'expires_in' => $access_token->getClaim('exp') - $access_token->getClaim('iat')]));
    $response = $response->withStatus(200);
    $response = $response->withHeader('Content-type', 'application/json');
    return $response;
});


/**
 * 获取大学英语四六级成绩 V1
 * ===============================================
 * GET                   /v1/cet_score/{name}/{numbers}
 *
 * HEADERS
 *      Authorization    Bearer {access_token}
 * ===============================================
 * {name}               姓名
 * {numbers}            准考证号
 * {access_token}       授权token
*/
$app->get('/v1/cet_score/{name}/{numbers}', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!in_array('read', $app->jwt->scope)) {
        $response->getBody()->write(json_encode(['error' => 'Permission denied']));
        $response = $response->withStatus(403);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    require_once 'v1/cet_score/cet_score.php';
    return cet_score::get($request, $response, $arguments);
});


/**
 * 获取大连工业大学学生个人信息 V1
 * ===============================================
 * GET                   /v1/dlpu/userinfo/{username}
 *
 * HEADERS
 *      Authorization    Bearer {access_token}
 *      password         {password}
 * ===============================================
 * {username}           登陆账号, 这里为学号
 * {access_token}       授权token
 * {password}           username的登陆密码
 */
$app->get('/v1/dlpu/userinfo/{username}', function(ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!in_array('read', $app->jwt->scope)) {
        $response->getBody()->write(json_encode(['error' => 'Permission denied']));
        $response = $response->withStatus(403);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    require_once 'v1/dlpu/slim_handle.php';
    return (new slim_handle($request, $response, $arguments))->userinfo();
});


/**
 * 获取大连工业大学学生成绩 V1
 * ===============================================
 * GET                   /v1/dlpu/usergrade/{username}/[{kksj}]
 *
 * HEADERS
 *      Authorization    Bearer {access_token}
 *      password         {password}
 * ===============================================
 * {username}           登陆账号, 这里为学号
 * {kksj}               可选项 开课时间 即查询某学期的成绩 默认为空查询所有学期 查询格式为 2014-2015-2(2014-2015学年第二学期)
 * {access_token}       授权token
 * {password}           username的登陆密码
 */
$app->get('/v1/dlpu/usergrade/{username}/[{kksj}]', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!in_array('read', $app->jwt->scope)) {
        $response->getBody()->write(json_encode(['error' => 'Permission denied']));
        $response = $response->withStatus(403);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    require_once 'v1/dlpu/slim_handle.php';
    return (new slim_handle($request, $response, $arguments))->usergrade();
});

/**
 * 获取大连工业大学教务处 公告信息
 * ===============================================
 * GET                   /v1/dlpu/announcement/{username}
 *
 * HEADERS
 *      Authorization    Bearer {access_token}
 *      password         {password}
 * ===============================================
 * {username}           登陆账号, 这里为学号
 * {access_token}       授权token
 * {password}           username的登陆密码
 */
$app->get('/v1/dlpu/announcement/{username}', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!in_array('read', $app->jwt->scope)) {
        $response->getBody()->write(json_encode(['error' => 'Permission denied']));
        $response = $response->withStatus(403);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    require_once 'v1/dlpu/slim_handle.php';
    return (new slim_handle($request, $response, $arguments))->announcement();
});


$app->get('/v1/dlpu/curriculum_theory/{username}/{semester}/{weeks}', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!in_array('read', $app->jwt->scope)) {
        $response->getBody()->write(json_encode(['error' => 'Permission denied']));
        $response = $response->withStatus(403);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    require_once 'v1/dlpu/slim_handle.php';
    return (new slim_handle($request, $response, $arguments))->curriculum_theory();
});



$app->run();
