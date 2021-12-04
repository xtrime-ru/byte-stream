<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Amp\ByteStream\Test;

use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\StreamException;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Pipeline;
use function Amp\ByteStream\parseLineDelimitedJson;

class ParseLineDelimitedJsonTest extends AsyncTestCase
{
    public function test(): void
    {
        $result = Pipeline\toArray(parseLineDelimitedJson(new InMemoryStream(\implode("\n", [
            \json_encode(['foo' => "\nbar\r\n"]),
            \json_encode(['foo' => []]),
        ]))));

        self::assertEquals([
            (object) ['foo' => "\nbar\r\n"],
            (object) ['foo' => []],
        ], $result);
    }

    public function testInvalidJson(): void
    {
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Failed to parse JSON');

        Pipeline\toArray(parseLineDelimitedJson(new InMemoryStream('{')));
    }
}
