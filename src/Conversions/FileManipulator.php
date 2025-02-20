<?php

namespace Programic\MediaLibrary\Conversions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Programic\MediaLibrary\Conversions\Actions\PerformConversionAction;
use Programic\MediaLibrary\Conversions\ImageGenerators\ImageGeneratorFactory;
use Programic\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
use Programic\MediaLibrary\MediaCollections\Filesystem;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;
use Programic\MediaLibrary\Support\TemporaryDirectory;

class FileManipulator
{
    public function createDerivedFiles(
        Media $media,
        array $onlyConversionNames = [],
        bool $onlyMissing = false,
        bool $withResponsiveImages = false
    ): void {
        if (! $this->canConvertMedia($media)) {
            return;
        }

        [$queuedConversions, $conversions] = ConversionCollection::createForMedia($media)
            ->filter(function (Conversion $conversion) use ($onlyConversionNames) {
                if (count($onlyConversionNames) === 0) {
                    return true;
                }

                return in_array($conversion->getName(), $onlyConversionNames);
            })
            ->filter(fn (Conversion $conversion) => $conversion->shouldBePerformedOn($media->collection_name))
            ->partition(fn (Conversion $conversion) => $conversion->shouldBeQueued());

        $this
            ->performConversions($conversions, $media, $onlyMissing)
            ->dispatchQueuedConversions($media, $queuedConversions, $onlyMissing)
            ->generateResponsiveImages($media, $withResponsiveImages);
    }

    public function performConversions(
        ConversionCollection $conversions,
        Media $media,
        bool $onlyMissing = false
    ): self {
        $conversions = $conversions
            ->when(
                $onlyMissing,
                fn (ConversionCollection $conversions) => $conversions->reject(function (Conversion $conversion) use ($media) {
                    $relativePath = $media->getPath($conversion->getName());

                    if ($rootPath = config("filesystems.disks.{$media->disk}.root")) {
                        $relativePath = str_replace($rootPath, '', $relativePath);
                    }

                    return Storage::disk($media->disk)->exists($relativePath);
                })
            );

        if ($conversions->isEmpty()) {
            return $this;
        }

        $temporaryDirectory = TemporaryDirectory::create();

        $copiedOriginalFile = app(Filesystem::class)->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(Str::random(32).'.'.$media->extension)
        );

        $conversions->each(fn (Conversion $conversion) => (new PerformConversionAction)->execute($conversion, $media, $copiedOriginalFile));

        $temporaryDirectory->delete();

        return $this;
    }

    protected function dispatchQueuedConversions(
        Media $media,
        ConversionCollection $conversions,
        bool $onlyMissing = false
    ): self {
        if ($conversions->isEmpty()) {
            return $this;
        }

        $performConversionsJobClass = config(
            'media-library.jobs.perform_conversions',
            PerformConversionsJob::class
        );

        /** @var PerformConversionsJob $job */
        $job = (new $performConversionsJobClass($conversions, $media, $onlyMissing))
            ->onConnection(config('media-library.queue_connection_name'))
            ->onQueue(config('media-library.queue_name'));

        config('media-library.queue_conversions_after_database_commit')
            ? dispatch($job)->afterCommit()
            : dispatch($job);

        return $this;
    }

    protected function generateResponsiveImages(Media $media, bool $withResponsiveImages): self
    {
        if (! $withResponsiveImages) {
            return $this;
        }

        if (! count($media->responsive_images)) {
            return $this;
        }

        $generateResponsiveImagesJobClass = config(
            'media-library.jobs.generate_responsive_images',
            GenerateResponsiveImagesJob::class
        );

        /** @var GenerateResponsiveImagesJob $job */
        $job = (new $generateResponsiveImagesJobClass($media))
            ->onConnection(config('media-library.queue_connection_name'))
            ->onQueue(config('media-library.queue_name'));

        config('media-library.queue_conversions_after_database_commit')
            ? dispatch($job)->afterCommit()
            : dispatch($job);

        return $this;
    }

    protected function canConvertMedia(Media $media): bool
    {
        $imageGenerator = ImageGeneratorFactory::forMedia($media);

        return $imageGenerator ? true : false;
    }
}
