<?php

namespace CodeWorx\Http\Exceptions;

use CodeWorx\Http\HttpResponse;
use function json_encode;

class ResponseErrorException extends HttpClientException
{
    /**
     * Holds the response
     *
     * @var \CodeWorx\Http\HttpResponse
     */
    protected $response;

    /**
     * ResponseErrorException constructor.
     *
     * @param \CodeWorx\Http\HttpResponse $response
     */
    public function __construct(HttpResponse $response)
    {
        $this->response = $response;

        parent::__construct(json_encode($response->getBody()), $response->getStatusCode());
    }

    /**
     * Retrieves the response
     *
     * @return \CodeWorx\Http\HttpResponse
     */
    public function getResponse(): HttpResponse
    {
        return $this->response;
    }
}
