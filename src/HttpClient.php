<?php

namespace CodeWorx\Http;

use function in_array;

/**
 * HTTP client library
 * ===================
 *
 * This class provides a simple wrapper on cURL requests. All requests start with a static method call
 * that creates a new request which further methods can be called on. If all request properties are set,
 * the actual cURL call can be performed using `$request->run()`.
 *
 * Request bodies are automatically stringified according to the request content type. While that defaults
 * to application/x-www-form-urlencoded, it can be set to JSON or multipart/formdata by using the
 * appropriate shorthands (`->asJson()` and `->asBlob()` respectively), or any custom value using
 * `->withContentType('application/x-foo-type')`.
 *
 * Request headers, query params and even the URL can be dynamically modified until the final `->run()`
 * call by using the chainable methods, for example `->withHeader()` or `->withParam()`.
 *
 * To set a common base URL, you can use the static `http::setBaseUrl()` method. Any subsequent HTTP calls
 * will merge their request URL with the base URL, which is particularly helpful with API calls.
 *
 * @example GET request:
 *          HttpClient::get('http:///example.org')->run();
 *
 * @example PATCH request with JSON body:
 *          HttpClient::patch('http:///example.org', [ 'body field' => true ])->asJson()->run();
 *
 * @example blob POST request:
 *          HttpClient::post('http:///example.org', [ 'image' => $imageHandle ])->asBlob()->run();
 *
 * @example Complex request:
 *          HttpClient::put('https:///example.org', [])
 *            ->withHeader('X-MSGR-PPL', 'v-1.124.2')
 *            ->withParam('foo', 'bar')
 *            ->withCurlOption(CURLOPT_TIMEOUT, 100)
 *            ->run(/* collect response headers: * / true);
 */
class HttpClient
{
    /**
     * Holds the base URL
     *
     * @var string
     */
    protected static $baseUrl = '';

    /**
     * Sets the base URL
     *
     * @param string $baseUrl
     */
    public static function setBaseUrl(string $baseUrl): void
    {
        static::$baseUrl = $baseUrl;
    }

    /**
     * Shorthand request creator for chaining
     *
     * @param string                                    $method  HTTP request method
     * @param string                                    $url     Request URL
     * @param array                                     $query   Query parameters to append to the URL
     * @param array                                     $headers Request headers to include with the request
     * @param \CodeWorx\Http\Stream|string|array $body    Request payload to include, either as a raw string or an array of fields
     *
     * @return \CodeWorx\Http\HttpRequest
     * @throws \InvalidArgumentException
     * @example HttpClient::request('GET', '/')->run();
     */
    public static function request(
        string $method = HttpRequest::METHOD_GET,
        string $url = '/',
        array $query = [],
        array $headers = [],
        $body = null
    ): HttpRequest {
        $request = new HttpRequest($method, static::resolveUrl($url));

        if (! empty($query)) {
            $request->withParams($query);
        }

        if (! empty($headers)) {
            $request->withHeaders($headers);
        }

        return $body && in_array($method, HttpRequest::METHODS_WITH_BODY, true)
            ? $request->withBody($body)
            : $request;
    }

    /**
     * Creates a new GET request
     *
     * @param string $url     Request URL
     * @param array  $query   Query parameters to append to the URL
     * @param array  $headers Request headers to include with the request
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     * @throws \InvalidArgumentException
     */
    public static function get(string $url, array $query = [], array $headers = []): HttpRequest
    {
        return static::request(HttpRequest::METHOD_GET, $url, $query, $headers);
    }

    /**
     * Creates a new DELETE request
     *
     * @param string $url     Request URL
     * @param array  $query   Query parameters to append to the URL
     * @param array  $headers Request headers to include with the request
     *
     * @return \CodeWorx\Http\HttpRequest
     * @throws \InvalidArgumentException
     */
    public static function delete(string $url, array $query = [], array $headers = []): HttpRequest
    {
        return static::request(HttpRequest::METHOD_DELETE, $url, $query, $headers);
    }

    /**
     * Creates a new HEAD request
     *
     * @param string $url     Request URL
     * @param array  $query   Query parameters to append to the URL
     * @param array  $headers Request headers to include with the request
     *
     * @return \CodeWorx\Http\HttpRequest
     * @throws \InvalidArgumentException
     */
    public static function head(string $url, array $query = [], array $headers = []): HttpRequest
    {
        return static::request(HttpRequest::METHOD_HEAD, $url, $query, $headers);
    }

    /**
     * Creates a new POST request
     *
     * @param string                                    $url     Request URL
     * @param \CodeWorx\Http\Stream|string|array $body    Request payload to include, either as a raw string or an array of fields
     * @param array                                     $query   Query parameters to append to the URL
     * @param array                                     $headers Request headers to include with the request
     *
     * @return \CodeWorx\Http\HttpRequest
     * @throws \InvalidArgumentException
     *
     */
    public static function post(string $url, $body, array $query = [], array $headers = []): HttpRequest
    {
        return static::request(
            HttpRequest::METHOD_POST,
            $url,
            $query,
            $headers,
            $body
        );
    }

    /**
     * Creates a new PUT request
     *
     * @param string                                    $url     Request URL
     * @param \CodeWorx\Http\Stream|string|array $body    Request payload to include, either as a raw string or an array of fields
     * @param array                                     $query   Query parameters to append to the URL
     * @param array                                     $headers Request headers to include with the request
     *
     * @return \CodeWorx\Http\HttpRequest
     * @throws \InvalidArgumentException
     */
    public static function put(string $url, $body, array $query = [], array $headers = []): HttpRequest
    {
        return static::request(
            HttpRequest::METHOD_PUT,
            $url,
            $query,
            $headers,
            $body
        );
    }

    /**
     * Creates a new PATCH request
     *
     * @param string                                    $url     Request URL
     * @param \CodeWorx\Http\Stream|string|array $body    Request payload to include, either as a raw string or an array of fields
     * @param array                                     $query   Query parameters to append to the URL
     * @param array                                     $headers Request headers to include with the request
     *
     * @return \CodeWorx\Http\HttpRequest
     * @throws \InvalidArgumentException
     */
    public static function patch(string $url, $body, array $query = [], array $headers = []): HttpRequest
    {
        return static::request(
            HttpRequest::METHOD_PATCH,
            $url,
            $query,
            $headers,
            $body
        );
    }

    /**
     * Resets the base URL
     *
     * @returns void
     */
    public static function deleteBaseUrl(): void
    {
        static::$baseUrl = '';
    }

    /**
     * Prepends the base URL, if set
     *
     * @param string $url
     *
     * @return string
     */
    protected static function resolveUrl(string $url): string
    {
        if (! static::$baseUrl) {
            return $url;
        }

        return static::$baseUrl . ltrim($url, '/');
    }
}
