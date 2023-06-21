<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace CodeWorx\Http\Tests;

use CodeWorx\Http\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{

    public function testGetMessage(): void
    {
        $message200 = Status::getMessage(200);
        $this->assertEquals('OK', $message200);
        $message404 = Status::getMessage(404);
        $this->assertEquals('Not Found', $message404);
    }

    public function testGetHeaderLine(): void
    {
        $headerLine = Status::getHeaderLine(200);
        $this->assertEquals('HTTP/1.1 200 OK', $headerLine);
        $headerLine = Status::getHeaderLine(404);
        $this->assertEquals('HTTP/1.1 404 Not Found', $headerLine);
    }

    public function testIsErrorCode(): void
    {
        $this->assertTrue(Status::isErrorCode(400), 400);
        $this->assertTrue(Status::isErrorCode(500), 500);
        $this->assertTrue(Status::isErrorCode(599), 599);
        $this->assertFalse(Status::isErrorCode(100), 100);
        $this->assertFalse(Status::isErrorCode(200), 200);
        $this->assertFalse(Status::isErrorCode(300), 300);
        $this->assertFalse(Status::isErrorCode(399), 399);
    }
}
