<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace CodeWorx\Http;

use ExternalTestServer;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    use ExternalTestServer;

    public function testGet(): void
    {
        $statusCode = HttpClient::get(static::$getUrl)
            ->run()
            ->getStatusCode();

        $this->assertEquals(200, $statusCode);
    }

    public function testPost(): void
    {
        $response = HttpClient::post(static::$postUrl, ['foo' => 'bar'])->run();
        $data = $response->getParsedBody();

        $this->assertEquals('POST', $data['method']);
        $this->assertEquals(['foo' => 'bar'], $data['body']);
    }

    public function testPut(): void
    {
        $request = HttpClient::put(static::$putUrl, ['foo' => 'bar']);
        $request->withHeader('Expect');
        $response = $request->run();
        $data = $response->getParsedBody();

        $this->assertEquals('PUT', $data['method']);
        $this->assertEquals(['foo' => 'bar'], $data['body']);
    }

    public function testPatch(): void
    {
        $response = HttpClient::patch(static::$patchUrl, ['foo' => 'bar'])->run();
        $data = $response->getParsedBody();

        $this->assertEquals('PATCH', $data['method']);
        $this->assertEquals(['foo' => 'bar'], $data['body']);
    }

    public function testDelete(): void
    {
        $statusCode = HttpClient::delete(static::$deleteUrl)
            ->run()
            ->getStatusCode();

        $this->assertEquals(200, $statusCode);
    }

    public function testHead(): void
    {
        $statusCode = HttpClient::head(static::$headUrl)
            ->run()
            ->getStatusCode();

        $this->assertEquals(200, $statusCode);
    }

    public function testSetBaseUrl(): void
    {
        HttpClient::setBaseUrl('foo');
        $request = HttpClient::get('bar');

        $this->assertEquals('foobar', $request->getUrl());
    }

    public function testDeleteBaseUrl(): void
    {
        HttpClient::setBaseUrl('foo');
        $request1 = HttpClient::get('bar');

        $this->assertEquals('foobar', $request1->getUrl());

        HttpClient::deleteBaseUrl();
        $request2 = HttpClient::get('bar');

        $this->assertEquals('bar', $request2->getUrl());
    }

    public function testRequest(): void
    {
        $request = HttpClient::request('GET', static::$getUrl, ['foo' => 'bar'], ['X-Test' => 'True'], ['baz' => 'quz']);

        $this->assertEquals('bar', $request->getParam('foo'));
        $this->assertEquals('True', $request->getHeader('X-Test'));
    }
}
