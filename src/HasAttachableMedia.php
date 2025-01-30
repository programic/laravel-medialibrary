<?php

namespace Programic\MediaLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Programic\MediaLibrary\MediaCollections\Models\Media;

interface HasAttachableMedia
{
    public function attachableMedia(): MorphToMany;

    /**
     * @param  bool  $detaching  Detach media missing in $ids?
     */
    public function attachMedia(array|Media|Collection $ids, bool $detaching): array;

    public function detachMedia(array|Media|Collection $ids): int;

    //    public function hasAttachableMedia(string $collectionName = ''): bool;
    //
    //    public function getAttachableMedia(string $collectionName = 'default', array|callable $filters = []): Collection;
}
