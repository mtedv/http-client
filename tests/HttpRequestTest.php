<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace CodeWorx\Http;

use ExternalTestServer;
use InvalidArgumentException;
use CodeWorx\Http\Exceptions\ConnectionException;
use CodeWorx\Http\Exceptions\HttpClientException;
use CodeWorx\Http\Exceptions\ResponseErrorException;
use CodeWorx\Http\Exceptions\SslCertificateException;
use CodeWorx\Http\Exceptions\UnresolvableHostException;
use PHPUnit\Framework\TestCase;
use const CURLOPT_BINARYTRANSFER;

class HttpRequestTest extends TestCase
{
    use ExternalTestServer;

    public function testCreatesInstance(): void
    {
        $request = new HttpRequest('GET', 'https://www.google.com');

        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testCreatesInstanceWithLowerCaseMethod(): void
    {
        $request = new HttpRequest('get', 'https://www.google.com');

        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testCreatesInstanceWithLocalUri(): void
    {
        $request = new HttpRequest('get', '/foo/bar');

        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testBailsOnCreatingInstanceForInvalidMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HttpRequest('brew', '/coffee');
    }

    public function testAsBlob(): void
    {
        $stream = Stream::open('php://temp', 'w+');
        $stream->write('foo');

        $request = new HttpRequest('post', '/');
        $request->withBody($stream);
        $request->asBlob();
        $this->assertEquals(true, $request->getCurlOption(CURLOPT_BINARYTRANSFER));
        $body = $request->getBody();
        $body->rewind();

        $this->assertEquals('foo', $body->getContents());
    }

    public function testWithHeader(): void
    {
        $request = new HttpRequest('get', '/foo/bar');
        $request->withHeader('Test-Header', 'foo');
        $request->withHeader('Test-Header', 'bar');

        $this->assertEquals(['foo', 'bar'], $request->getHeader('test-HEADER', false));
    }

    public function testWithHeaderReplacesHeaders(): void
    {
        $request = new HttpRequest('get', '/foo/bar');
        $request->withHeader('Test-Header', 'foo');
        $request->withHeader('Test-Header', 'bar', true);

        $this->assertEquals(['bar'], $request->getHeader('test-HEADER', false));
    }

    public function testWithHeaders(): void
    {
        $request = new HttpRequest('get', '/');
        $request
            ->withHeaders([
                              'X-Test-Header-1' => 'test 1',
                              'X-Test-Header-2' => 'test 2',
                              'X-Test-Header-3' => 'test 3',
                              'X-Test-Header' => 'test 4a',
                          ], false)
            ->withHeaders([
                              'X-Test-Header' => 'test 4b',
                          ], false)
            ->withHeaders([
                              'X-Test-Header' => 'test 4c',
                          ], false);

        $this->assertEquals(
            [
                'x-test-header-1' => ['test 1'],
                'x-test-header-2' => ['test 2'],
                'x-test-header-3' => ['test 3'],
                'x-test-header' => ['test 4a', 'test 4b', 'test 4c'],
            ],
            $request->getHeaders()
        );
    }

    public function testGetHeader(): void
    {
        $request = new HttpRequest('get', '/foo/bar');
        $request->withHeader('TeSt-HeAdER', 'foo');

        $this->assertEquals('foo', $request->getHeader('TeSt-HeAdER'));
    }

    public function testGetHeaderIsCaseInsensitive(): void
    {
        $request = new HttpRequest('get', '/foo/bar');
        $request->withHeader('TeSt-HeAdER', 'foo');

        $this->assertEquals('foo', $request->getHeader('test-header'));
    }

    public function testGetHeaders(): void
    {
        $request = new HttpRequest('get', '/');
        $request
            ->withHeaders([
                              'X-Test-Header-1' => 'test 1',
                              'X-Test-Header-2' => 'test 2',
                              'X-Test-Header-3' => 'test 3',
                              'X-Test-Header' => 'test 4a',
                          ]);

        $this->assertEquals(
            [
                'x-test-header-1' => ['test 1'],
                'x-test-header-2' => ['test 2'],
                'x-test-header-3' => ['test 3'],
                'x-test-header' => ['test 4a'],
            ],
            $request->getHeaders()
        );
    }

    public function testWithoutHeader(): void
    {
        $request = new HttpRequest('get', '/');
        $request->withHeader('X-X', 'test');
        $this->assertEquals('test', $request->getHeader('X-X'));
        $request->withoutHeader('X-X');
        $this->assertNull($request->getHeader('X-X'));
    }

    public function testWithParam(): void
    {
        $request = new HttpRequest('get', '/foo/bar');
        $request->withParam('test', 'foo');

        $this->assertEquals('foo', $request->getParam('test'));
    }

    public function testGetParam(): void
    {
        $request = new HttpRequest('get', '/foo/bar');
        $request->withParam('foo', 'bar');

        $this->assertEquals('bar', $request->getParam('foo'));
    }

    public function testGetParamFromUrl(): void
    {
        $request = new HttpRequest('get', '/foo/bar?baz=quz');

        $this->assertEquals('quz', $request->getParam('baz'));
    }

    public function testWithParams(): void
    {
        $request = new HttpRequest('get', '/');
        $request->withParams([
                                 'foo' => 'bar',
                                 'baz' => 'quz',
                             ]);

        $this->assertEquals('bar', $request->getParam('foo'));
        $this->assertEquals('quz', $request->getParam('baz'));
        # TODO: Test
    }

    public function testWithoutParam(): void
    {
        $request = new HttpRequest('get', '/');
        $request->withParam('foo', 'bar');
        $this->assertEquals('bar', $request->getParam('foo'));
        $request->withoutParam('foo');
        $this->assertNull($request->getParam('foo'));
    }

    public function testRun(): void
    {
        $request = new HttpRequest('get', static::$getUrl);

        $this->assertInstanceOf(HttpRequest::class, $request);

        $response = $request->run();

        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(HttpResponse::class, $response);
    }

    public function testRunWithQueryParameters(): void
    {
        $request = new HttpRequest('get', static::$getUrl);
        $request->withParams(['foo' => 'bar', 'baz' => 'quz']);
        $response = $request->run();

        $this->assertStringEndsWith('?foo=bar&baz=quz', $response->getParsedBody()['uri']);
    }

    public function testHandleQueryParametersAndUrlWithQuery(): void
    {
        $request = new HttpRequest('get', static::$getUrl . '?test=123');
        $request->withParams(['foo' => 'bar', 'baz' => 'quz']);
        $response = $request->run();

        $this->assertStringEndsWith('?test=123&foo=bar&baz=quz', $response->getParsedBody()['uri']);
    }

    public function testRunShouldThrowUnresolvableHostExceptionOnErrorsResolvingHosts(): void
    {
        $this->expectException(UnresolvableHostException::class);
        (new HttpRequest('get', 'http://missing.invalid'))->run();
    }

    public function testRunShouldThrowSslConnectionExceptionOnErrorsConnectingViaSsl(): void
    {
        $this->expectException(SslCertificateException::class);
        (new HttpRequest('get', 'https://127.0.0.1:18027'))->run();
    }

    public function testRunShouldThrowConnectionExceptionOnErrorsDuringConnection(): void
    {
        $this->expectException(ConnectionException::class);
        $request = new HttpRequest('get', static::$testServer . '/errors/infinite-redirect');
        $request->followRedirects();
        $request->run();
    }

    public function testRunShouldThrowHttpClientExceptionOnInvalidStatusCodes(): void
    {
        $this->expectException(HttpClientException::class);
        $request = new HttpRequest('get', static::$testServer . '/status/500');
        $request->run();
    }

    public function testDefaultContentTypeIsSet(): void
    {
        $request = new HttpRequest('get', '/');

        $this->assertEquals('application/x-www-form-urlencoded', HttpRequest::CONTENT_TYPE_FORM);
        $this->assertEquals(HttpRequest::CONTENT_TYPE_FORM, $request->getContentType());
    }

    public function testWithContentType(): void
    {
        $request = new HttpRequest('get', '/');

        $request->withContentType('test');
        $this->assertEquals('test', $request->getContentType());
    }

    public function testAsJson(): void
    {
        $request = new HttpRequest('get', '/');

        $this->assertEquals(HttpRequest::CONTENT_TYPE_FORM, $request->getContentType());

        $request->asJson();

        $this->assertEquals(HttpRequest::CONTENT_TYPE_JSON, $request->getContentType());
    }

    public function testWithAuthorizationBasic(): void
    {
        $request = new HttpRequest('get', static::$getUrl);

        $request->withAuthorization(HttpRequest::AUTHORIZATION_BASIC, 'user', 'pass');
        /** @noinspection SpellCheckingInspection */
        $this->assertEquals('Basic dXNlcjpwYXNz', $request->getHeader('Authorization'));
    }

    public function testWithAuthorizationBearer(): void
    {
        $request = new HttpRequest('get', static::$getUrl);

        $request->withAuthorization(HttpRequest::AUTHORIZATION_BEARER, 'token');
        $this->assertEquals('Bearer token', $request->getHeader('Authorization'));
    }

    public function testWithAuthorizationDigest(): void
    {
        $request = new HttpRequest('get', static::$getUrl);

        $request->withAuthorization(HttpRequest::AUTHORIZATION_DIGEST, 'token');
        $this->assertEquals('Digest token', $request->getHeader('Authorization'));
    }

    public function testWithAuthorizationOAuth(): void
    {
        $request = new HttpRequest('get', static::$getUrl);

        $request->withAuthorization(HttpRequest::AUTHORIZATION_OAUTH, 'token');
        $this->assertEquals('OAuth token', $request->getHeader('Authorization'));
    }

    public function testWithAuthorizationBailsOnInvalidAuthorizationMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new HttpRequest('get', static::$getUrl);
        $request->withAuthorization('invalid', 'token');
    }

    public function testWithBody(): void
    {
        $request = new HttpRequest('post', '/');
        $request->withBody('foo=bar');
        $this->assertEquals('foo=bar', $request->getEncodedBody());
    }

    public function testWithBodyBailsOnInvalidRequestMethods(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new HttpRequest('get', '/');
        $request->withBody('');
    }

    public function testWithBodyAndCustomBodyLength(): void
    {
        $request = new HttpRequest('post', '/');
        $request->withBody('foo=bar', 500);
        $this->assertEquals('foo=bar', $request->getEncodedBody());
        $this->assertEquals(500, $request->getHeader('Content-Length'));
    }

    public function testWithBodyJson(): void
    {
        $request = new HttpRequest('post', '/');
        $request->asJson();
        $request->withBody(['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', $request->getEncodedBody());
    }

    public function testWithBodyUrlEncoded(): void
    {
        $request = new HttpRequest('post', '/');
        $request->withContentType(HttpRequest::CONTENT_TYPE_FORM);
        $request->withBody(['foo' => 'bar']);
        $this->assertEquals('foo=bar', $request->getEncodedBody());
    }

    public function testWithBodyPlain(): void
    {
        $request = new HttpRequest('post', '/');
        $request->withContentType(HttpRequest::CONTENT_TYPE_TEXT);
        $request->withBody(0x23532);
        $this->assertEquals('144690', $request->getEncodedBody());
    }

    public function testWithBodyFallback(): void
    {
        $request = new HttpRequest('post', '/');
        $request->withContentType('something/different');
        $request->withBody(0x23532);
        $this->assertEquals(0x23532, $request->getEncodedBody());
    }

    public function testGetBody(): void
    {
        $request = new HttpRequest('post', '/');
        $request->withBody(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $request->getBody());
    }

    public function testWithoutBody(): void
    {
        $request = new HttpRequest('post', '/');
        $request->withBody(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $request->getBody());
        $request->withoutBody();
        $this->assertEquals(null, $request->getBody());
    }

    public function testWithCurlOption(): void
    {
        $request = new HttpRequest('get', '/');
        $request->withCurlOption(123, 'bar');
        $this->assertEquals('bar', $request->getCurlOption(123));
    }

    public function testWithoutCurlOption(): void
    {
        $request = new HttpRequest('get', '/');
        $request->withCurlOption(123, 'bar');
        $this->assertEquals('bar', $request->getCurlOption(123));
        $request->withoutCurlOption(123);
        $this->assertNull($request->getCurlOption(123));
    }

    public function testGetCurlOptions(): void
    {
        $request = new HttpRequest('get', '/');
        $request->withCurlOption(1, 456);
        $request->withCurlOption(2, 'test');
        $request->withCurlOption(3);
        $this->assertEquals(
            [
                1 => 456,
                2 => 'test',
                3 => true,
            ],
            $request->getCurlOptions()
        );
    }

    public function testGetUrl(): void
    {
        $request = new HttpRequest('get', 'https://user:pass@host.tld:123/path#fragment');
        $this->assertEquals('https://user:pass@host.tld:123/path#fragment', $request->getUrl());
    }

    public function testWithUrl(): void
    {
        $request = new HttpRequest('get', '/wrong/address');
        $request->withUrl('/foo/bar');
        $this->assertEquals('/foo/bar', $request->getUrl());
    }

    public function testReadsErrorResponseBody(): void
    {
        $request = new HttpRequest('get', static::$testServer . '/status/400');

        try {
            $request->run();
        } catch (ResponseErrorException $exception) {
            $response = $exception->getResponse();

            /** @noinspection UnnecessaryAssertionInspection */
            $this->assertInstanceOf(HttpResponse::class, $response);
            $this->assertIsArray($response->getParsedBody());
            $this->assertEquals([
                                    'status' => 400,
                                    'message' => 'error message',
                                ], $response->getParsedBody());
        }
    }

    public function testBinaryUpload(): void
    {
        $stream = Stream::open(__DIR__ . '/fixtures/image.png');
        $request = new HttpRequest('post', static::$uploadUrl);
        $response = $request
            ->withBody($stream)
            ->asBlob()
            ->run();

        $stream->rewind();
        $this->assertEquals($stream->getContents(), $response->getBody());
    }

    public function testBinaryResponse(): void
    {
        $request = new HttpRequest('GET', static::$testServer . '/type/binary');
        $response = $request->run();

        $this->assertInstanceOf(Stream::class, $response->getParsedBody());

        $stream = Stream::open(__DIR__ . '/fixtures/image.png');
        $stream->rewind();
        $this->assertEquals($stream->getContents(), $response->getParsedBody()->getContents());
    }

    public function testJsonResponse(): void
    {
        $request = new HttpRequest('GET', static::$testServer . '/type/json');
        $response = $request->run();

        $this->assertEquals(['property' => 'value'], $response->getParsedBody());
        $this->assertEquals('{"property":"value"}', $response->getBody());
    }

    public function testJsonUtf8Response(): void
    {
        $request = new HttpRequest('GET', static::$testServer . '/type/json-utf8');
        $response = $request->run();

        $this->assertEquals(['property' => 'value'], $response->getParsedBody());
        $this->assertEquals('{"property":"value"}', $response->getBody());
    }

    public function testPlainText(): void
    {
        $request = new HttpRequest('GET', static::$testServer . '/type/plain');
        $response = $request->run();

        $this->assertEquals('success', $response->getParsedBody());
        $this->assertEquals('success', $response->getBody());
    }

    public function testMultipartRequest(): void
    {
        $body = new FormData(['foo' => 'bar']);
        $request = new HttpRequest('POST', static::$testServer . '/multipart');
        $response = $request
            ->withBody($body)
            ->run()
            ->getParsedBody();

        $this->assertArrayHasKey('foo', $response['fields']);
        $this->assertEquals('bar', $response['fields']['foo']);
    }

    public function testMultipartRequestWithAttachment(): void
    {
        $body = new FormData(['foo' => 'bar']);
        $body->addFile(
            'test_file',
            Stream::open(__DIR__ . '/fixtures/image.png'),
            'image/png'
        );

        $request = new HttpRequest('POST', static::$testServer . '/multipart');

        $response = $request
            ->withBody($body)
            ->run()
            ->getParsedBody();

        $this->assertArrayHasKey('foo', $response['fields']);
        $this->assertEquals('bar', $response['fields']['foo']);
        $this->assertArrayHasKey('test_file', $response['files']);
    }

    public function testMultipartRequestWithCustomNamedAttachment(): void
    {
        $body = new FormData(['foo' => 'bar']);
        $body->addFile(
            'test_file',
            Stream::open(__DIR__ . '/fixtures/image.png'),
            'image/png',
            'custom-filename.png'
        );

        $request = new HttpRequest('POST', static::$testServer . '/multipart');

        $response = $request
            ->withBody($body)
            ->run()
            ->getParsedBody();

        $this->assertArrayHasKey('foo', $response['fields']);
        $this->assertEquals('bar', $response['fields']['foo']);
        $this->assertArrayHasKey('test_file', $response['files']);
        $this->assertEquals('custom-filename.png', $response['files']['test_file']['name']);
    }
}
