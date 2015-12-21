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
    }
]));


$app->add(new HttpBasicAuthentication([
    "path" => "/token",
    "users" => [
        "user0" => "user0password"
    ]
]));

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
    if(!in_array('read', $app->jwt->scope)) return $response->withStatus(401);

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
$app->get('/v1/dlpu/userinfo/{username}', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!in_array('read', $app->jwt->scope)) return $response->withStatus(401);
    if(is_null($request->getHeaderLine('password'))) return $response->withStatus(401);

    require_once 'v1/dlpu/student_login.php';
    $student_login = new student_login($arguments['username'], $request->getHeaderLine('password'));
    if($student_login->isSuccess()) {
        $student_login_cookie = $student_login->getCookie();
        require_once 'v1/dlpu/student_information.php';
        $student_information = new student_information($student_login_cookie);
        $userinfo = $student_information->getInfo();

        $response->getBody()->write(json_encode(['messages' => 'OK', 'data' => $userinfo]));
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }else{
        $response->getBody()->write(json_encode(['messages' => 'wrong student_id or password', 'data' => NULL]));
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
});


/**
 * 获取大连工业大学学生成绩 V1
 * ===============================================
 * GET                   /v1/dlpu/usergrade/{username}
 *
 * HEADERS
 *      Authorization    Bearer {access_token}
 *      password         {password}
 * ===============================================
 * {username}           登陆账号, 这里为学号
 * {access_token}       授权token
 * {password}           username的登陆密码
 */
$app->get('/v1/dlpu/usergrade/{username}', function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($app) {
    if(!in_array('read', $app->jwt->scope)) return $response->withStatus(401);
    if(is_null($request->getHeaderLine('password'))) return $response->withStatus(401);

    require_once 'v1/dlpu/student_login.php';
    $student_login = new student_login($arguments['username'], $request->getHeaderLine('password'));
    if($student_login->isSuccess()) {
        $student_login_cookie = $student_login->getCookie();
        require_once 'v1/dlpu/student_grade.php';
        $student_grade = new student_grade($student_login_cookie);
        $usergrade = $student_grade->getGrade();

        $response->getBody()->write(json_encode(['messages' => 'OK', 'data' => $usergrade]));
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }else{
        $response->getBody()->write(json_encode(['messages' => 'wrong student_id or password', 'data' => NULL]));
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
});

$app->run();
