<?php

namespace CodeWorx\Http;

use InvalidArgumentException;
use CodeWorx\Http\Exceptions\ConnectionException;
use CodeWorx\Http\Exceptions\HttpClientException;
use CodeWorx\Http\Exceptions\ResponseErrorException;
use CodeWorx\Http\Exceptions\SslCertificateException;
use CodeWorx\Http\Exceptions\UnresolvableHostException;
use Psr\Http\Message\StreamInterface;
use function array_key_exists;
use function base64_encode;
use function count;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function explode;
use function floor;
use function http_build_query;
use function in_array;
use function is_array;
use function json_encode;
use function parse_str;
use function parse_url;
use function str_replace;
use function strlen;
use function strtolower;
use function strtoupper;
use function trim;
use const CURLE_COULDNT_CONNECT;
use const CURLE_COULDNT_RESOLVE_HOST;
use const CURLE_FAILED_INIT;
use const CURLE_GOT_NOTHING;
use const CURLE_READ_ERROR;
use const CURLE_RECV_ERROR;
use const CURLE_SSL_CACERT;
use const CURLE_SSL_CACERT_BADFILE;
use const CURLE_SSL_CERTPROBLEM;
use const CURLE_SSL_CIPHER;
use const CURLE_SSL_CONNECT_ERROR;
use const CURLE_SSL_ENGINE_NOTFOUND;
use const CURLE_SSL_ENGINE_SETFAILED;
use const CURLE_SSL_PEER_CERTIFICATE;
use const CURLE_SSL_PINNEDPUBKEYNOTMATCH;
use const CURLE_TOO_MANY_REDIRECTS;
use const CURLINFO_HTTP_CODE;
use const CURLOPT_BINARYTRANSFER;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_FAILONERROR;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_HTTP200ALIASES;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_INFILE;
use const CURLOPT_INFILESIZE;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_READFUNCTION;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_UPLOAD;
use const CURLOPT_URL;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_URL_QUERY;

/**
 * Class HttpRequest
 *
 * @package CodeWorx\Http
 */
class HttpRequest extends HttpMessage
{
    protected const CURL_CONNECT_TIMEOUT = 30;

    protected const CURL_TIMEOUT = 30;

    /**
     * Holds the request method
     *
     * @var string
     */
    protected $method;

    /**
     * Holds the request URL
     *
     * @var string
     */
    protected $url;

    /**
     * Holds the curl options to set
     *
     * @var array
     */
    protected $curlOptions = [];

    /**
     * Holds the query parameters
     *
     * @var array
     */
    protected $query = [];

    /**
     * Holds the request body
     *
     * @var array|string
     */
    protected $body;

    /**
     * Holds the curl instance
     *
     * @var resource
     */
    protected $request;

    /**
     * HttpRequest constructor.
     *
     * @param string $method Request method
     * @param string $url    Request URL
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $method, string $url)
    {
        $this->request = curl_init();
        $this->method = strtoupper($method);

        switch ($this->method) {
            case static::METHOD_GET:
                break;

            case static::METHOD_POST:
                $this->withCurlOption(CURLOPT_POST);
                break;

            /*
             // In theory, PHP curl supports the curl PUT method natively. This, however, causes curl
             // to send an "Expect: 100 Continue" header, which totally fucks some web servers.
             // So instead of that, PUT has just been moved one block further down to the CUSTOMREQUEST
             // curl option, which works fine.
            case static::METHOD_PUT:
                $this->withCurlOption(CURLOPT_PUT);
                break;
            */

            // All other methods must use CURLOPT_CUSTOMREQUEST
            case static::METHOD_PUT:
            case static::METHOD_HEAD:
            case static::METHOD_DELETE:
            case static::METHOD_PATCH:
                $this->withCurlOption(CURLOPT_CUSTOMREQUEST, $method);
                break;

            default:
                throw new InvalidArgumentException("Unknown request method $method");
        }

        $this->withUrl($url);
    }

    /**
     * Assembles the request headers by transforming them from `key => value` to `"key: value"` pairs
     *
     * @param array $headers Request headers to assemble
     *
     * @return array         Assembled request headers
     */
    protected static function assembleHeaders(array $headers): array
    {
        $assembledHeaders = [];

        foreach ($headers as $header => $lines) {
            foreach ($lines as $line) {
                $assembledHeaders[] = "$header: $line";
            }
        }

        return $assembledHeaders;
    }

    /**
     * Applies all configured cURL options
     *
     * @param resource $request cURL resource to configure
     * @param array    $options cURL options to apply
     *
     * @return resource
     */
    protected static function applyCurlOptions($request, array $options)
    {
        foreach ($options as $option => $value) {
            curl_setopt($request, $option, $value);
        }

        return $request;
    }

    /**
     * Encodes the request body in any supported encoding
     *
     * @param \Psr\Http\Message\StreamInterface|array|string $body
     * @param string                                         $encoding
     *
     * @return string
     */
    protected static function encodeBody($body, string $encoding): string
    {
        if ($body instanceof StreamInterface) {
            return (string) $body;
        }

        if ($body instanceof FormData) {
            return $body;
        }

        switch ($encoding) {
            case static::CONTENT_TYPE_JSON:
                return json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            case static::CONTENT_TYPE_FORM:
                return is_array($body)
                    ? http_build_query($body)
                    : $body;

            case static::CONTENT_TYPE_TEXT:
                return (string) $body;

            case static::CONTENT_TYPE_MULTIPART:
                // TODO: Implement multipart messages
                break;
        }

        return $body;
    }

    /**
     * Reads the raw resource from a stream
     *
     * @param \Psr\Http\Message\StreamInterface $stream
     *
     * @return resource|null
     */
    protected static function getRawStream(StreamInterface $stream)
    {
        $clonedStream = clone $stream;

        return $clonedStream->detach();
    }

    /**
     * Sets a CURL option on the current request
     *
     * @param int   $option Expected to be a CURLOPT_ constant
     * @param mixed $value  Any valid value for the option. If no value is passed, defaults to true
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withCurlOption(int $option, $value = true): self
    {
        $this->curlOptions[$option] = $value;

        return $this;
    }

    /**
     * Removes a curl option
     *
     * @param int $option
     *
     * @return \CodeWorx\Http\HttpRequest
     */
    public function withoutCurlOption(int $option): self
    {
        unset($this->curlOptions[$option]);

        return $this;
    }

    /**
     * Sets the timeout in seconds
     *
     * @param int $timeout
     * @return $this
     */
    public function withTimeout(int $timeout): self {
        $this->curlOptions[CURLOPT_TIMEOUT] = $timeout;
        $this->curlOptions[CURLOPT_CONNECTTIMEOUT] = $timeout;
        return $this;
    }

    /**
     * If a timeout is set, return it
     *
     * @return int|null
     */
    public function getTimeout(): ?int {
        return $this->curlOptions[CURLOPT_TIMEOUT]??null;
    }

    /**
     * Retrieves the value of a previously set curl option by name
     *
     * @param int $option
     *
     * @return mixed|null
     */
    public function getCurlOption(int $option)
    {
        return $this->curlOptions[$option] ?? null;
    }

    /**
     * Retrieves all previously set curl options
     *
     * @return array
     */
    public function getCurlOptions(): array
    {
        return $this->curlOptions;
    }

    /**
     * Appends multiple headers to the request
     *
     * @param array $headers    Request headers as a key => value mapping
     * @param bool  $replaceAll Whether to replace existing headers
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withHeaders(array $headers, bool $replaceAll = true): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value, $replaceAll);
        }

        return $this;
    }

    /**
     * Appends a header to the request
     *
     * @param string     $name    Name of the header
     * @param mixed|null $value   Value of the header
     * @param bool       $replace Whether to replace any existing headers or append to the list
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withHeader(string $name, $value = '', bool $replace = false): self
    {
        $lowercaseHeaderName = strtolower($name);

        if (! isset($this->headers[$lowercaseHeaderName])) {
            $this->headers[$lowercaseHeaderName] = [$value];

            return $this;
        }

        if ($replace) {
            $this->headers[$lowercaseHeaderName] = [$value];
        } else {
            $this->headers[$lowercaseHeaderName][] = $value;
        }

        return $this;
    }

    /**
     * Deletes a header from the request
     *
     * @param string $name
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withoutHeader(string $name): self
    {
        unset($this->headers[strtolower($name)]);

        return $this;
    }

    /**
     * Appends multiple query params to the request
     *
     * @param array $params Query parameters as a key => value mapping
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withParams(array $params): self
    {
        foreach ($params as $name => $value) {
            $this->withParam($name, $value);
        }

        return $this;
    }

    /**
     * Appends a query param to the request
     *
     * @param string     $name  Name of the parameter
     * @param mixed|null $value Value of the parameter
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withParam(string $name, $value = ''): self
    {
        $this->query[$name] = $value;

        return $this;
    }

    /**
     * Retrieves a query param from the request
     *
     * @param string $name Name of the query parameter
     *
     * @return mixed|null
     */
    public function getParam(string $name)
    {
        return $this->query[$name] ?? null;
    }

    /**
     * Deletes a query param from the request
     *
     * @param string $name Name of the query parameter
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withoutParam(string $name): self
    {
        while (array_key_exists($name, $this->query)) {
            unset($this->query[$name]);
        }

        return $this;
    }

    /**
     * Sets the request URL
     *
     * @param string $url Request URL to set
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withUrl(string $url): self
    {
        // Parse the query string out of the URL
        $queryString = parse_url($url, PHP_URL_QUERY);

        if ($queryString) {
            // Parse the query string into an associative array
            parse_str($queryString, $query);

            // Remove the query string from the URL
            $url = str_replace('?' . $queryString, '', $url);

            // Append the query params
            $this->withParams($query);
        }

        $this->url = $url;

        return $this;
    }

    /**
     * Retrieves the request URL
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Advises cURL to follow redirects
     *
     * @param bool $followRedirects
     *
     * @return \CodeWorx\Http\HttpRequest
     */
    public function followRedirects(bool $followRedirects = true): self
    {
        return $this->withCurlOption(CURLOPT_FOLLOWLOCATION, $followRedirects);
    }

    /**
     * Sets the request body
     *
     * @param \Psr\Http\Message\StreamInterface|\CodeWorx\Http\FormData|string|array $body   Request payload to
     *                                                                                              include
     * @param int                                                                           $length Optional body
     *                                                                                              length. If omitted,
     *                                                                                              `strlen` will be
     *                                                                                              used to determine
     *                                                                                              the value.
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     * @throws \InvalidArgumentException
     */
    public function withBody($body, int $length = null): self
    {
        if (! in_array($this->method, static::METHODS_WITH_BODY, true)) {
            throw new InvalidArgumentException("Requests with method $this->method can't have a body");
        }

        $this->body = $body;

        if ($length !== null) {
            $this->withHeader(static::HEADER_CONTENT_LENGTH, $length, true);
        }

        return $this;
    }

    /**
     * Retrieves the request body
     *
     * @return \Psr\Http\Message\StreamInterface|array|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Retrieves the encoded request body
     *
     * @return string
     */
    public function getEncodedBody(): string
    {
        return static::encodeBody($this->body, $this->getContentType());
    }

    /**
     * Deletes the body from the request
     *
     * @return \CodeWorx\Http\HttpRequest
     */
    public function withoutBody(): self
    {
        $this->body = null;

        return $this->withoutHeader(static::HEADER_CONTENT_LENGTH);
    }

    /**
     * Appends an authorization header
     *
     * @param string $type            Authentication type
     * @param string $usernameOrToken Username for basic, token for bearer and parameters for digest authentication
     * @param string $password        Only required for basic authentication
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     * @throws \InvalidArgumentException
     */
    public function withAuthorization(string $type, string $usernameOrToken, string $password = ''): self
    {
        switch ($type) {
            case static::AUTHORIZATION_BASIC:
                $credentials = base64_encode("{$usernameOrToken}:{$password}");
                break;

            case static::AUTHORIZATION_BEARER:
            case static::AUTHORIZATION_DIGEST:
            case static::AUTHORIZATION_OAUTH:
                $credentials = $usernameOrToken;
                break;

            default:
                throw new InvalidArgumentException("Invalid or unsupported authorization type '$type'.");
        }

        return $this->withHeader(static::HEADER_AUTHORIZATION, "$type $credentials");
    }

    /**
     * @param string $path
     * @param bool $force Do not validate if file exists
     * @return $this
     */
    public function withSSLClientCertificate(string $path, bool $force = false): self
    {
        if (!$force && !file_exists($path)) {
            throw new InvalidArgumentException("Certificate file ' . $path . ' not found");
        }

        $this->sslClientCertificate = $path;

        return $this;
    }

    /**
     * @param string $path
     * @param bool $force Do not validate if file exists
     * @return $this
     */
    public function withSSLClientKey(string $path, bool $force = false): self
    {
        if (!$force && !file_exists($path)) {
            throw new InvalidArgumentException("Certificate file ' . $path . ' not found");
        }

        $this->sslClientKey = $path;

        return $this;
    }

    public function withSSLClientCertificatePassword(string $password): self
    {
        $this->sslClientCertificatePassword = $password;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function withSSLClientKeyPassword(string $password): self
    {
        $this->sslClientKeyPassword = $password;

        return $this;
    }

    /**
     * Marks the request as a JSON request, which will convert the request body if it's an array.
     *
     * @param bool $asJson
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function asJson(bool $asJson = true): self
    {
        return $this->withContentType($asJson ? static::CONTENT_TYPE_JSON : static::CONTENT_TYPE_TEXT);
    }

    /**
     * Applies a content type to the request.
     *
     * @param string $contentType
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function withContentType(string $contentType): self
    {
        return $this->withHeader(static::HEADER_CONTENT_TYPE, $contentType);
    }

    /**
     * Marks the request as a binary transfer, so responses will be read as a blob.
     *
     * @param bool $asBlob
     *
     * @return \CodeWorx\Http\HttpRequest Current instance for chaining
     */
    public function asBlob($asBlob = true): self
    {
        if ($this->getContentType() === static::CONTENT_TYPE_FORM) {
            $this->withContentType(static::CONTENT_TYPE_BINARY);
        }

        return $this->withCurlOption(CURLOPT_BINARYTRANSFER, $asBlob);
    }

    /**
     * Executes the request
     *
     * @return \CodeWorx\Http\HttpResponse
     * @throws \CodeWorx\Http\Exceptions\ConnectionException
     * @throws \CodeWorx\Http\Exceptions\HttpClientException
     * @throws \CodeWorx\Http\Exceptions\ResponseErrorException
     * @throws \CodeWorx\Http\Exceptions\SslCertificateException
     * @throws \CodeWorx\Http\Exceptions\UnresolvableHostException
     */
    public function run(): HttpResponse
    {
        // Unless we have no query, build the query string and append it to the URL
        if (! empty($this->query)) {
            $queryString = http_build_query($this->query);

            $this->url .= '?' . $queryString;
        }

        // Set the URL
        $this->withCurlOption(CURLOPT_URL, $this->url);

        // Holds all response headers
        $responseHeaders = [];

        // Alias *all* HTTP error codes as 200 and work them out on the client
        $this->withCurlOption(CURLOPT_HTTP200ALIASES, Status::ERROR_CODES);
        $this->withCurlOption(CURLOPT_FAILONERROR, false);

        // If the current request method may have a body according to the standards
        // and we've got a body, serialize it and append it to the request
        if ($this->body && in_array($this->method, static::METHODS_WITH_BODY, true)) {
            if ($this->body instanceof StreamInterface) {
                $bodyLength = $this->body->getSize();

                $this->withCurlOption(CURLOPT_INFILE, static::getRawStream($this->body));
                $this->withCurlOption(CURLOPT_INFILESIZE, $bodyLength);

                // CURLOPT_UPLOAD makes the request a streaming upload - and sets the request method to PUT
                $this->withCurlOption(CURLOPT_UPLOAD);
                $this->withCurlOption(CURLOPT_CUSTOMREQUEST, $this->method);
            } elseif ($this->body instanceof FormData) {
                $this->withCurlOption(CURLOPT_CUSTOMREQUEST, $this->method);
                $this->withCurlOption(CURLOPT_UPLOAD);
                $this->withCurlOption(CURLOPT_READFUNCTION, [$this->body, 'curl_read']);
                $bodyLength = $this->body->getContentLength();

                $this->withContentType($this->body->getContentType());
            } else {
                $bodyString = $this->getEncodedBody();
                $bodyLength = strlen($bodyString);

                $this->withCurlOption(CURLOPT_POSTFIELDS, $bodyString);
            }

            if (! $this->hasHeader(static::HEADER_CONTENT_LENGTH)) {
                $this->withHeader(static::HEADER_CONTENT_LENGTH, $bodyLength, true);
            }
        }

        // Set all generic CURL options
        $this->withCurlOption(CURLOPT_HTTPHEADER, static::assembleHeaders($this->headers));
        
        // TODO: Set as default if no timeouts provided
        // $this->withCurlOption(CURLOPT_TIMEOUT, static::CURL_TIMEOUT);
        // $this->withCurlOption(CURLOPT_CONNECTTIMEOUT, static::CURL_CONNECT_TIMEOUT);

        $this->withCurlOption(CURLOPT_RETURNTRANSFER);
        $this->withCurlOption(
            CURLOPT_HEADERFUNCTION,
            function(/** @noinspection PhpUnusedParameterInspection */ $curl, string $header) use (&$responseHeaders
            ): int {
                $length = strlen($header);
                $parts = explode(':', $header, 2);

                if (count($parts) < 2) {
                    return $length;
                }

                $name = strtolower(trim($parts[0]));

                if (! array_key_exists($name, $responseHeaders)) {
                    $responseHeaders[$name] = [trim($parts[1])];
                } else {
                    $responseHeaders[$name][] = trim($parts[1]);
                }

                return $length;
            });

        // Add SSL Client Certificate
        if ($this->sslClientCertificate) {
            $this->withCurlOption(CURLOPT_SSLCERT, $this->getSslClientCertificate());

            // And if needed the certificates password
            if ($this->sslClientCertificatePassword) {
                $this->withCurlOption(CURLOPT_SSLCERTPASSWD, $this->getSslClientCertificatePassword());
            }
        }

        // Add SSL Client Key
        if ($this->sslClientKey) {
            $this->withCurlOption(CURLOPT_SSLKEY, $this->getSslClientKey());

            if ($this->sslClientKeyPassword) {
                $this->withCurlOption(CURLOPT_SSLKEYPASSWD, $this->getSslClientKeyPassword());
            }
        }

        // Apply all curl options
        $request = static::applyCurlOptions($this->request, $this->curlOptions);

        // Execute the request
        $responseBody = curl_exec($request);

        // Retrieve the status code
        $statusCode = curl_getinfo($request, CURLINFO_HTTP_CODE);

        $response = new HttpResponse($statusCode, $responseBody, $responseHeaders);

        // Retrieve the error code
        $errorCode = curl_errno($request);
        $errorMessage = curl_error($request) ?? 'none';

        // Close the request
        curl_close($request);

        // check if the CURL error code is higher than 0 or the status code is not from the 2xx range
        if ($errorCode > 0 || (int) floor($statusCode / 100) > 3) {
            switch ($errorCode) {
                case 0:
                    throw new ResponseErrorException($response);

                case CURLE_COULDNT_RESOLVE_HOST:
                    throw new UnresolvableHostException($this->url, $errorMessage, $errorCode);

                case CURLE_SSL_CONNECT_ERROR:
                case CURLE_SSL_CACERT:
                case CURLE_SSL_CACERT_BADFILE:
                case CURLE_SSL_CERTPROBLEM:
                case CURLE_SSL_CIPHER:
                case CURLE_SSL_ENGINE_NOTFOUND:
                case CURLE_SSL_ENGINE_SETFAILED:
                case CURLE_SSL_PEER_CERTIFICATE:
                case CURLE_SSL_PINNEDPUBKEYNOTMATCH:
                    throw new SslCertificateException("SSL connection failed: $errorMessage", $errorCode);

                case CURLE_COULDNT_CONNECT:
                case CURLE_TOO_MANY_REDIRECTS:
                case CURLE_GOT_NOTHING:
                case CURLE_FAILED_INIT:
                case CURLE_READ_ERROR:
                case CURLE_RECV_ERROR:
                    throw new ConnectionException("Unable to connect to remote server: $errorMessage", $errorCode);

                // We were unable to determine the exact error, so we'll throw a generic one instead
                default:
                    throw new HttpClientException(
                        "Request failed with status $statusCode (cURL error $errorCode: $errorMessage)",
                        $errorCode
                    );
            }
        }

        return $response;
    }
}
