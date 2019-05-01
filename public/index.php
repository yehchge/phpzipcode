<?php

if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) { return false; }

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use zipcodetw\zipcodetw;

$PROJECT_ROOT = dirname(__FILE__)."/..";

require $PROJECT_ROOT . '/vendor/autoload.php';
require $PROJECT_ROOT . "/src/zipcodetw/config.php";
require $PROJECT_ROOT . "/src/zipcodetw/zipcodetw.php";

$app = new \Slim\App;

$app->GET('/about', function (Request $request, Response $response, array $argv) use ($PROJECT_ROOT) {

    $loader = new \Twig\Loader\FilesystemLoader($PROJECT_ROOT.'/public/templates');

    $twig = new \Twig\Environment($loader);

    // Get assets path
    $baseUri = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME']));

    $template = $twig->load("about.html");

    $data = array('uri' => array('base' => $baseUri));

    $tpl = $template->render($data);

    $response->getBody()->write($tpl);

    return $response;
});


$app->GET('/api/find', function (Request $request, Response $response, array $argv) {
    $addr = isset($_GET['address'])?trim($_GET['address']):'';
    return json_encode(array('result'=>zipcodetw::find($addr)));
});

$app->GET('/',function (Request $request, Response $response, array $args) use ($PROJECT_ROOT) {

    $loader = new \Twig\Loader\FilesystemLoader($PROJECT_ROOT.'/public/templates');

    $twig = new \Twig\Environment($loader);

    // Get assets path
    $baseUri = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME']));

    $template = $twig->load("finder.html");

    $data = array('uri' => array('base' => $baseUri));

    $tpl = $template->render($data);

    $response->getBody()->write($tpl);

    return $response;
});
$app->run();
