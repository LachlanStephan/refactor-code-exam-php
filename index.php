<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$searcher = new \Squiz\PhpCodeExam\Searcher();

logMsg(
    sprintf('Got request %s', json_encode($request)),
    'request'
);

function checkTerm($request) 
{
    $param = $request->query->get('term');
    if ($param !== null) {
        return $param;
    }
    return false;
}

function sendRes($data = [], $status = 200)
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
        sendRes($result);
    }
    sendRes([], 400);
}

try {

    if (preg_match('/contents/', $path) !== 0) {
        header('Content-Type: application/json; charset=utf-8');
        $term = checkTerm($request);
        if ($term) {
            searchExecute($searcher, $term, 'content');
        } else {
            // probably want a 404 page or something
        }
    }

    if (preg_match('/tags/', $path) !== 0) {
        header('Content-Type: application/json; charset=utf-8');
        $term = checkTerm($request);
        if ($term) {
            searchExecute($searcher, $term, 'tag');
        }
    }

    if (preg_match('/pages/', $path) !== 0) {

        $paths = explode('/', $path);
        $id = array_pop($paths);
        if (is_int($id)) {
            $data = $searcher->getPageById($id);
            sendRes($data, 200);
        }
    }

    /**
     * TODO 
     * swap if stmt to switch case 
     * add below into its own function -> call on default 
     * move onto tidying the search class
     */

    $data = $searcher->allData;

    $null = NULL;
    $response = empty($data) ? new Response($null, Response::HTTP_NO_CONTENT) : new JsonResponse($data, Response::HTTP_ACCEPTED);
    error_log(
        sprintf('Sent response %s', $response->getContent()),
        0,
        __DIR__ . '/logs/response.log'
    );
    $response->send();

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
