<?php

namespace App\Tests\Unit\Service;

use App\Service\LogFileParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LogFileParserTest extends TestCase
{
    private string $filePath;
    private LogFileParser $logFileParser;

    protected function setUp(): void
    {
        $this->filePath = dirname(__DIR__, 2) . '/Fixtures/Files/import_error_test.log';
        $this->logFileParser = new LogFileParser();

        file_put_contents(
            $this->filePath,
            "Error while reading file with ID 12: XML file 'malformed.xml' is empty or malformed.\n"
        );

        file_put_contents(
            $this->filePath,
            "Error while reading file with ID 1: JSON file 'malformed.json' is empty or malformed.",
            FILE_APPEND
        );
    }

    public function testReturnsMessageAfterParsingFile(): void
    {
        $message = $this->logFileParser->parseFile($this->filePath, 12);
        $expected = "XML file 'malformed.xml' is empty or malformed.";

        $this->assertSame($expected, $message);
    }


    public function testReturnsNullIfIdNotFound(): void
    {
        $message = $this->logFileParser->parseFile($this->filePath, 50);

        $this->assertNull($message);
    }

    public function testThrowsIfFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);

        $nonexistentFile = dirname(__DIR__, 2) . '/Fixtures/Files/inexistent.log';

        $this->logFileParser->parseFile($nonexistentFile, 1);
    }

    public function testMatchesIdCorrectly(): void
    {
        $message = $this->logFileParser->parseFile($this->filePath, 1);

        $this->assertSame("JSON file 'malformed.json' is empty or malformed.", $message);
    }

    protected function tearDown(): void
    {
        if (isset($this->filePath)) {
            file_put_contents($this->filePath, "");   
        }
        parent::tearDown();
    }
}