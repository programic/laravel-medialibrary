<?php

namespace Programic\MediaLibrary\Tests\TestSupport;

use Programic\MediaLibrary\Conversions\Conversion;
use Programic\MediaLibrary\Support\FileNamer\FileNamer;

class TestFileNamer extends FileNamer
{
    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        return "prefix_{$fileName}_suffix---{$conversion->getName()}";
    }

    public function responsiveFileName(string $fileName): string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        return "prefix_{$fileName}_suffix";
    }
}
