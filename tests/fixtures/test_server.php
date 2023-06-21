<?php /** @noinspection PhpUnhandledExceptionInspection */

require_once __DIR__ . '/../../vendor/autoload.php';

use CodeWorx\Http\Stream;

if (! function_exists('getallheaders')) {
    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     * @return string[] The HTTP header key/value pairs.
     */
    function getallheaders()
    {
        $headers = [];
        $copy_server = [
            'CONTENT_TYPE' => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5' => 'Content-Md5',
        ];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $key = substr($key, 5);

                if (! isset($copy_server[$key], $_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (! isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = $_SERVER['PHP_AUTH_PW'] ?? '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}

$uri = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$urlencodedBodyFields = $_POST;
$queryParameters = $_GET;
$rawBody = file_get_contents('php://input');
$body = null;

if (empty($urlencodedBodyFields) && $rawBody !== '') {
    if ($headers && isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
        $jsonBody = json_decode($rawBody);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $body = $jsonBody;
        }
    } else {
        parse_str($rawBody, $body);
    }
} elseif (! empty($urlencodedBodyFields)) {
    $body = $urlencodedBodyFields;
} else {
    $body = $rawBody;
}

$sendResponse = function($statusCode = 200) use ($method, $uri, $headers, $body, $queryParameters) {
    $response = json_encode(
        [
            'method' => $method,
            'uri' => $_SERVER['REQUEST_URI'],
            'path' => $uri,
            'headers' => $headers,
            'body' => $body,
            'query' => $queryParameters,
            'server' => $_SERVER,
            'raw_body' => file_get_contents('php://input'),
            'raw_post' => $_POST,
            'raw_files' => $_FILES,
        ],
        JSON_PRETTY_PRINT
    );

    header('Content-Type: application/json');

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        $response = '{"error":"bad request"}';
    } else {
        http_response_code($statusCode);
    }

    echo $response;

    exit(0);
};
$abort = function($statusCode = 400, $message = null) {
    http_response_code($statusCode);

    if ($message !== null) {
        echo json_encode($message);
    }

    exit(1);
};

switch ($uri) {
    case '/method/get':
        if ($method !== 'GET') {
            $abort(405);
        }

        $sendResponse(200);
        break;

    case '/method/delete':
        if ($method !== 'DELETE') {
            $abort(405);
        }

        $sendResponse(200);
        break;

    case '/method/head':
        if ($method !== 'HEAD') {
            $abort(405);
        }

        $sendResponse(200);
        break;

    case '/method/post':
        if ($method !== 'POST') {
            $abort(405);
        }

        $sendResponse(200);
        break;

    case '/method/put':
        if ($method !== 'PUT') {
            $abort(405);
        }

        $sendResponse(200);
        break;

    case '/method/patch':
        if ($method !== 'PATCH') {
            $abort(405);
        }

        $sendResponse(200);
        break;

    case '/upload':
        $uploadedFileStream = Stream::open('php://input');

        echo $uploadedFileStream->getContents();
        exit(0);
        break;

    case '/multipart':
        header('Content-Type: application/json');
        echo json_encode([
                             'fields' => $_POST,
                             'files' => $_FILES,
                         ]);
        exit(0);
        break;

    case '/status/204':
        http_response_code(204);
        exit(0);
        break;

    case '/status/400':
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 400, 'message' => 'error message']);
        exit(0);
        break;

    case '/status/404':
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status' => 404, 'message' => 'error message']);
        exit(0);
        break;

    case '/status/500':
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['status' => 500, 'message' => 'error message']);
        exit(0);
        break;

    case '/errors/infinite-redirect':
        http_response_code(301);
        header('Location: /errors/infinite-redirect');
        exit(0);
        break;

    case '/type/plain':
        header('Content-Type: text/plain');
        echo 'success';
        exit(0);
        break;

    case '/type/json':
        header('Content-Type: application/json');
        echo json_encode(['property' => 'value']);
        exit(0);
        break;

    case '/type/json-utf8':
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['property' => 'value']);
        exit(0);
        break;

    case '/type/binary':
        $stream = Stream::open(__DIR__ . '/image.png');
        $stream->rewind();

        header('Content-Type: image/png');
        echo $stream->getContents();
        exit(0);
        break;
}

$abort(404, [
    'status' => 404,
    'message' => "No such test endpoint: $uri",
]);

exit(0);
