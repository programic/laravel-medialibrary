<?php

namespace Programic\MediaLibrary;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Programic\MediaLibrary\Conversions\Conversion;
use Programic\MediaLibrary\MediaCollections\FileAdder;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 *
 * @property bool $registerMediaConversionsUsingModelInstance
 * @property ?\Programic\MediaLibrary\MediaCollections\MediaCollection $mediaCollections
 */
interface HasMedia
{
    public function media(): MorphMany;

    public function addMedia(string|UploadedFile $file): FileAdder;

    public function copyMedia(string|UploadedFile $file): FileAdder;

    public function hasMedia(string $collectionName = ''): bool;

    public function getMedia(string $collectionName = 'default', array|callable $filters = []): Collection;

    public function clearMediaCollection(string $collectionName = 'default'): HasMedia;

    public function clearMediaCollectionExcept(string $collectionName = 'default', array|Collection $excludedMedia = []): HasMedia;

    public function shouldDeletePreservingMedia(): bool;

    public function loadMedia(string $collectionName);

    public function addMediaConversion(string $name): Conversion;

    public function registerMediaConversions(?Media $media = null): void;

    public function registerMediaCollections(): void;

    public function registerAllMediaConversions(): void;

    public function getMediaModel(): string;
}
