<?php

namespace Programic\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class FunctionalityNotAvailable extends Exception
{
    public static function mediaLibraryProRequired(): self
    {
        return new static('You need to have media library pro installed to make this work.');
    }
}
