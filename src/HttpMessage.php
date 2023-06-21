<?php

namespace CodeWorx\Http;

use function strtolower;

abstract class HttpMessage
{
    public const AUTHORIZATION_BASIC = 'Basic';

    public const AUTHORIZATION_BEARER = 'Bearer';

    public const AUTHORIZATION_DIGEST = 'Digest';

    public const AUTHORIZATION_OAUTH = 'OAuth';

    public const CONTENT_TYPE_BINARY = 'application/octet-stream';

    public const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    public const CONTENT_TYPE_JSON = 'application/json';

    public const CONTENT_TYPE_JSON_UTF_8 = 'application/json; charset=utf-8';

    public const CONTENT_TYPE_MULTIPART = 'multipart/form-data';

    public const CONTENT_TYPE_TEXT = 'text/plain';

    protected const FIELD_BODY = 'body';

    protected const FIELD_HEADERS = 'headers';

    protected const FIELD_METHOD = 'method';

    protected const FIELD_QUERY = 'query';

    protected const FIELD_URL = 'url';

    public const HEADER_AUTHORIZATION = 'Authorization';

    public const HEADER_CONTENT_LENGTH = 'Content-Length';

    public const HEADER_CONTENT_TYPE = 'Content-Type';

    public const METHODS = [
        self::METHOD_GET,
        self::METHOD_DELETE,
        self::METHOD_HEAD,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_PATCH,
    ];

    public const METHODS_WITH_BODY = [
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_PATCH,
    ];

    public const METHOD_DELETE = 'DELETE';

    public const METHOD_GET = 'GET';

    public const METHOD_HEAD = 'HEAD';

    public const METHOD_PATCH = 'PATCH';

    public const METHOD_POST = 'POST';

    public const METHOD_PUT = 'PUT';

    /**
     * Holds the request headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * @var string Holds the path to the ssl client certificate
     */
    protected $sslClientCertificate = '';

    /**
     * @var string
     */
    protected $sslClientKey = '';

    /**
     * @var string
     */
    protected $sslClientCertificatePassword = '';

    /**
     * @var string
     */
    protected $sslClientKeyPassword = '';

    /**
     * Retrieves a request header by name
     *
     * @param string $name Name of the header
     * @param bool $first Whether to return to first header
     *
     * @return mixed|null
     */
    public function getHeader(string $name, bool $first = true)
    {
        $lowercaseHeaderName = strtolower($name);

        if (!isset($this->headers[$lowercaseHeaderName])) {
            return null;
        }

        return $first
            ? $this->headers[$lowercaseHeaderName][0] ?? null
            : $this->headers[$lowercaseHeaderName];
    }

    /**
     * Checks whether a specific header is set
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return !isset($this->headers[strtolower($name)]);
    }

    /**
     * Retrieves all request headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Retrieves the content type. Defaults to `application/x-www-form-urlencoded`.
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->getHeader(static::HEADER_CONTENT_TYPE) ?? static::CONTENT_TYPE_FORM;
    }

    /**
     * @return string
     */
    public function getSslClientCertificate(): string
    {
        return $this->sslClientCertificate;
    }

    /**
     * @return string
     */
    public function getSslClientCertificatePassword(): string
    {
        return $this->sslClientCertificatePassword;
    }

    /**
     * @return string
     */
    public function getSslClientKey(): string
    {
        return $this->sslClientKey;
    }

    /**
     * @return string
     */
    public function getSslClientKeyPassword(): string
    {
        return $this->sslClientKeyPassword;
    }

}
