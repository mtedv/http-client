<?php

namespace CodeWorx\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function strtolower;

use const JSON_ERROR_NONE;

/**
 * Class HttpResponse
 *
 * @package CodeWorx\Http
 */
class HttpResponse extends HttpMessage
{
    /**
     * Holds the HTTP response status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Holds the response body
     *
     * @var string
     */
    protected $body;

    /**
     * HttpResponse constructor.
     *
     * @param int    $statusCode
     * @param string $body
     * @param array  $headers
     */
    public function __construct(int $statusCode, string $body, array $headers)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->setHeaders($headers);
    }

    /**
     * Retrieves the HTTP response status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Retrieves the response body.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Retrieves the response body as a stream
     *
     * @return StreamInterface
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function getStream(): StreamInterface
    {
        $stream = Stream::open('php://memory', 'wb+');
        $stream->write($this->body);
        $stream->rewind();

        return $stream;
    }

    /**
     * Retrieves the parsed response body.
     *
     * @return string|array|Stream
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function getParsedBody()
    {
        // Content type -> to lowercase -> split on ";", use the first part
        $contentType = strtok(strtolower($this->getHeader(static::HEADER_CONTENT_TYPE) ?? ''), ';');

        switch ($contentType) {
            case static::CONTENT_TYPE_JSON:
                $parsedBody = json_decode($this->body, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('Invalid response JSON: ' . json_last_error_msg());
                }

                return $parsedBody;

            case static::CONTENT_TYPE_TEXT:
                return $this->body;

            default:
                return $this->getStream();
        }
    }

    /**
     * Sets all headers
     *
     * @param array $headers
     */
    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $values) {
            $this->headers[strtolower($name)] = $values;
        }
    }
}
