<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$searcher = new \Squiz\PhpCodeExam\Searcher();

function logMsg($message, $type = 'error')
{
    $logger = new \Squiz\PhpCodeExam\Logger();

    switch ($type) {
        case 'error':
            $file = __DIR__ . '/logs/error.log';
        case 'request':
            $file = __DIR__ . '/logs/request.log';
        case 'response':
            $file = __DIR__ . '/logs/response.log';
        default:
            $file = __DIR__ . '/logs/log.log';
    }
    $logger->log($message, $file);
}

function checkTerm($request) 
{
    $param = $request->query->get('term');
    if ($param !== null) {
        return $param;
    }
    return false;
}

function sendRes($status, $data = ["No data"])
{
    $response = new JsonResponse([
        "data" => $data,
    ], $status);

    logMsg(
        sprintf('Sent response %s', $response->getContent()),
        'response'
    );

    $response->send();
    exit(0);
}

function searchExecute($searcher, $term, $type)
{
    $result = $searcher->execute($term, $type);
    if ($result) {
        sendRes(200, $result);
    }
    sendRes(204);
}

function defaultResponse($searcher) 
{
        $data = $searcher->allData;

        $null = NULL;
        $response = empty($data) ? new Response($null, Response::HTTP_NO_CONTENT) : new JsonResponse($data, Response::HTTP_ACCEPTED);
        error_log(
            sprintf('Sent response %s', $response->getContent()),
            0,
            __DIR__ . '/logs/response.log'
        );
        $response->send();
}

logMsg(
    sprintf('Got request %s', json_encode($request)),
    'request'
);

try {
    // todo: refactor this into routes -> likely abstract into routes controller 
    switch(true) {
        case preg_match('/contents/', $path) !== 0: 
            header('Content-Type: application/json; charset=utf-8');
            $term = checkTerm($request);
            if ($term) {
                searchExecute($searcher, $term, 'content');
            } else {
                // probably want a 404 page or something
            }
        break;
        case preg_match('/tags/', $path) !== 0: 
            header('Content-Type: application/json; charset=utf-8');
            $term = checkTerm($request);
            if ($term) {
                searchExecute($searcher, $term, 'tags');
            } else {
                // probably want a 404 page or something
            }
        break;
        case preg_match('/pages/', $path) !== 0: 
            $paths = explode('/', $path);
            $id = (int)array_pop($paths);
            if ($id > 0) {
                $data = $searcher->getPageById($id);
                sendRes(200, $data);
            }
        break;
        default: 
            defaultResponse($searcher);
        break;
    }
} catch (Exception $ex) {
    new JsonResponse(['exception' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
    logMsg(
        sprintf('%s: %s', $ex->getCode(), $ex->getMessage())
    );
}

$response = new JsonResponse(['error' => 'Failed to get pages'], Response::HTTP_INTERNAL_SERVER_ERROR);
$response->send();
logMsg(
    sprintf('Failed to get pages'),
    'failure'
);