<?php /** @noinspection FopenBinaryUnsafeUsageInspection, PhpUnhandledExceptionInspection, PhpMethodMayBeStaticInspection */

namespace CodeWorx\Http;

use Exception;
use function ftell;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function filesize;
use function fopen;
use function is_resource;

class StreamTest extends TestCase
{
    public static $isFReadError = false;

    public function testConstructorThrowsExceptionOnInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @noinspection PhpParamsInspection */
        new Stream(true);
    }

    public function testConstructorInitializesProperties(): void
    {
        $handle = fopen('php://temp', 'r+');

        fwrite($handle, 'data');

        $stream = new Stream($handle);

        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());

        $stream->close();
    }

    public function testConstructorInitializesPropertiesWithRbPlus(): void
    {
        $handle = fopen('php://temp', 'rb+');

        fwrite($handle, 'data');

        $stream = new Stream($handle);

        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());

        $stream->close();
    }

    public function testStreamClosesHandleOnDestruct(): void
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);

        unset($stream);

        $this->assertFalse(is_resource($handle));
    }

    public function testConvertsToString(): void
    {
        $handle = fopen('php://temp', 'w+');

        fwrite($handle, 'data');

        $stream = new Stream($handle);

        $this->assertEquals('data', (string) $stream);
        $this->assertEquals('data', (string) $stream);

        $stream->close();
    }

    public function testGetsContents(): void
    {
        $handle = fopen('php://temp', 'w+');

        fwrite($handle, 'data');

        $stream = new Stream($handle);

        $this->assertEquals('', $stream->getContents());

        $stream->seek(0);

        $this->assertEquals('data', $stream->getContents());
        $this->assertEquals('', $stream->getContents());

        $stream->close();
    }

    public function testChecksEof(): void
    {
        $handle = fopen('php://temp', 'w+');

        fwrite($handle, 'data');

        $stream = new Stream($handle);

        $this->assertSame(4, $stream->tell(), 'Stream cursor already at the end');
        $this->assertFalse($stream->eof(), 'Stream still not eof');
        $this->assertSame('', $stream->read(1), 'Need to read one more byte to reach eof');
        $this->assertTrue($stream->eof());

        $stream->close();
    }

    public function testGetSize(): void
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');
        $stream = new Stream($handle);

        $this->assertEquals($size, $stream->getSize());

        // Load from cache
        $this->assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function testOverrideSize(): void
    {
        $size = 42;
        $handle = fopen(__FILE__, 'r');
        $stream = new Stream($handle, [
            'size' => $size
        ]);

        $this->assertEquals($size, $stream->getSize());

        // Load from cache
        $this->assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function testEnsuresSizeIsConsistent(): void
    {
        $h = fopen('php://temp', 'w+');

        $this->assertEquals(3, fwrite($h, 'foo'));

        $stream = new Stream($h);

        $this->assertEquals(3, $stream->getSize());
        $this->assertEquals(4, $stream->write('test'));
        $this->assertEquals(7, $stream->getSize());
        $this->assertEquals(7, $stream->getSize());

        $stream->close();
    }

    public function testProvidesStreamPosition(): void
    {
        $handle = fopen('php://temp', 'w+');
        $stream = new Stream($handle);

        $this->assertEquals(0, $stream->tell());

        $stream->write('foo');

        $this->assertEquals(3, $stream->tell());

        $stream->seek(1);

        $this->assertEquals(1, $stream->tell());
        $this->assertSame(ftell($handle), $stream->tell());

        $stream->close();
    }

    public function testDetachStreamAndClearProperties(): void
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        $this->assertSame($handle, $stream->detach());
        $this->assertIsResource($handle, 'Stream is not closed');
        $this->assertNull($stream->detach());

        $this->assertStreamStateAfterClosedOrDetached($stream);

        $stream->close();
    }

    public function testCloseResourceAndClearProperties(): void
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        $stream->close();

        $this->assertFalse(is_resource($handle));
        $this->assertStreamStateAfterClosedOrDetached($stream);
    }

    public function testStreamReadingWithZeroLength(): void
    {
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);

        $this->assertSame('', $stream->read(0));

        $stream->close();
    }

    public function testStreamReadingWithNegativeLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Length parameter cannot be negative');

        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);

        try {
            $stream->read(-1);
        } catch (Exception $e) {
            $stream->close();
            throw $e;
        }

        $stream->close();
    }

    public function testStreamReadingFreadError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to read from stream');

        self::$isFReadError = true;
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);

        try {
            $stream->read(1);
        } catch (Exception $e) {
            self::$isFReadError = false;

            $stream->close();

            throw $e;
        }

        self::$isFReadError = false;

        $stream->close();
    }

    public function testStreamRewinds(): void
    {
        $handle = fopen('php://temp', 'w+');
        $stream = new Stream($handle);

        $this->assertEquals(0, $stream->tell());

        $stream->write('foobar');

        $this->assertEquals(6, $stream->tell());

        $stream->seek(3);

        $this->assertEquals(3, $stream->tell());
        $this->assertSame(ftell($handle), $stream->tell());

        $stream->rewind();

        $this->assertEquals(0, $stream->tell());

        $stream->close();
    }

    private function assertStreamStateAfterClosedOrDetached(Stream $stream): void
    {
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $this->assertNull($stream->getSize());
        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getMetadata('foo'));

        $throws = function(callable $fn) {
            try {
                $fn();
            } catch (Exception $e) {
                $this->assertContains('Stream is detached', $e->getMessage());

                return;
            }

            $this->fail('Exception should be thrown after the stream is detached.');
        };

        $throws(function() use ($stream) {
            $stream->read(10);
        });
        $throws(function() use ($stream) {
            $stream->write('bar');
        });
        $throws(function() use ($stream) {
            $stream->seek(10);
        });
        $throws(function() use ($stream) {
            $stream->tell();
        });
        $throws(function() use ($stream) {
            $stream->eof();
        });
        $throws(function() use ($stream) {
            $stream->getContents();
        });
        $this->assertSame('', (string) $stream);
    }
}

function fread($handle, $length)
{
    return StreamTest::$isFReadError ? false : \fread($handle, $length);
}
