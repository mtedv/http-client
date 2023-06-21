<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace CodeWorx\Http;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class HttpResponseTest extends TestCase
{
    public function test__construct(): void
    {
        $response = new HttpResponse(200, '', []);

        $this->assertInstanceOf(HttpResponse::class, $response);
    }

    public function testGetHeader(): void
    {
        $response = new HttpResponse(200, '', [
            'X-Test' => ['foo', 'bar', 'baz'],
            'X-Test-2' => ['quz'],
        ]);

        $this->assertEquals('quz', $response->getHeader('X-Test-2'));
    }

    public function testGetLowerCaseHeader(): void
    {
        $response = new HttpResponse(200, '', [
            'X-Test' => ['foo', 'bar', 'baz'],
            'X-Test-2' => ['quz'],
        ]);

        $this->assertEquals('quz', $response->getHeader('x-test-2'));
    }

    public function testGetAllValuesForHeader(): void
    {
        $response = new HttpResponse(200, '', [
            'X-Test' => ['foo', 'bar', 'baz'],
            'X-Test-2' => ['quz'],
        ]);

        $this->assertEquals(['foo', 'bar', 'baz'], $response->getHeader('x-test', false));
    }

    public function testGetHeaders(): void
    {
        $response = new HttpResponse(200, '', [
            'X-Test' => ['foo', 'bar', 'baz'],
            'X-Test-2' => ['quz'],
        ]);

        $this->assertEquals([
                                'x-test' => ['foo', 'bar', 'baz'],
                                'x-test-2' => ['quz'],
                            ], $response->getHeaders());
    }

    public function testGetStatusCode(): void
    {
        $response = new HttpResponse(418, '', []);

        $this->assertEquals(418, $response->getStatusCode());
    }

    public function testGetBody(): void
    {
        $response = new HttpResponse(200, 'foo bar', []);

        $this->assertEquals('foo bar', $response->getBody());
    }

    public function testGetJsonBody(): void
    {
        $response = new HttpResponse(200, '{"property":"value"}', [
            'Content-Type' => ['application/json'],
        ]);

        $this->assertEquals(['property' => 'value'], $response->getParsedBody());
    }

    public function testGetPlainTextBody(): void
    {
        $response = new HttpResponse(200, 'hello world', [
            'Content-Type' => ['text/plain'],
        ]);

        $this->assertEquals('hello world', $response->getParsedBody());
    }

    public function testGetJsonUtf8Body(): void
    {
        $response = new HttpResponse(200, '{"property":"value"}', [
            'Content-Type' => ['application/json; charset=utf-8'],
        ]);

        $this->assertEquals(['property' => 'value'], $response->getParsedBody());
    }

    public function testGetJsonUtf8BodyWithNonStandardCasing(): void
    {
        $response = new HttpResponse(200, '{"property":"value"}', [
            'Content-Type' => ['application/JSON; CHARSET=UTF-8'],
        ]);

        $this->assertEquals(['property' => 'value'], $response->getParsedBody());
    }

    public function testBailsOnInvalidBodyJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/^Invalid response JSON: .*$/');

        $response = new HttpResponse(200, '{"invalid', [
            'Content-Type' => ['application/json; charset=utf-8'],
        ]);

        $response->getParsedBody();
    }
}
