<?php

namespace Programic\MediaLibrary\Tests\TestSupport;

use Programic\MediaLibrary\Conversions\Conversion;
use Programic\MediaLibrary\Support\FileNamer\FileNamer;

class RenameOriginalFileNamer extends FileNamer
{
    public function originalFileName(string $fileName): string
    {
        return 'renamed_original_file';
    }

    public function responsiveFileName(string $fileName): string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        return "prefix_{$fileName}_suffix";
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        return "prefix_{$fileName}_suffix---{$conversion->getName()}";
    }
}
