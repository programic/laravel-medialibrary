<?php

namespace Programic\MediaLibrary\Conversions\Events;

use Illuminate\Queue\SerializesModels;
use Programic\MediaLibrary\Conversions\Conversion;
use Programic\MediaLibrary\MediaCollections\Models\Media;

class ConversionHasBeenCompletedEvent
{
    use SerializesModels;

    public function __construct(public Media $media, public Conversion $conversion) {}
}
