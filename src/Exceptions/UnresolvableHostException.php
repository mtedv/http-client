<?php

namespace CodeWorx\Http\Exceptions;

use Throwable;
use function parse_url;

class UnresolvableHostException extends HttpClientException
{
    public function __construct(string $url, string $message, int $code = 0, Throwable $previous = null)
    {
        $parts = parse_url($url);
        $hostname = $parts['host'];

        parent::__construct("Could not resolve host $hostname", $code, $previous);
    }
}
