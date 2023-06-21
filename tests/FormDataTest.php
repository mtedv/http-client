<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace CodeWorx\Http\Tests;

use CodeWorx\Http\FormData;
use PHPUnit\Framework\TestCase;
use function strlen;

class FormDataTest extends TestCase
{
    public function testStringifies(): void
    {
        $formData = new FormData(['foo' => 'bar']);
        $stringified = (string) $formData;

        $this->assertIsString($stringified);
    }

    public function testGeneratesRandomBoundary(): void
    {
        $formData = new FormData();

        $this->assertEquals(24, strlen($formData->getBoundary()));
    }
}
