<?php

namespace Modules\Support\app\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\Media\MediaHelper;

/**
 * @property-read string $human_size
 * @property-read string $preview_image_url
 */
trait HasUploadedFile
{
    /**
     * Get the file's preview image.
     *
     * @return Attribute
     */
    public function previewImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->isImage() && isset($this->{$this->fileColumn ?? 'document'}['url'])
                ? $this->{$this->fileColumn ?? 'document'}['url']
                : MediaHelper::resolvePreviewImage(
                    $this->isImage()
                        ? "image"
                        : $this->{$this->fileColumn ?? 'document'}['extension']
                )
        );
    }

    /**
     * Determine if the file type is image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return strtok($this->{$this->fileColumn ?? 'document'}['mime'], '/') === 'image';
    }

    /**
     * Get the file's human size.
     *
     * @return Attribute
     */
    public function humanSize(): Attribute
    {
        return Attribute::make(
            get: fn() => humanFileSize($this->{$this->fileColumn ?? 'document'}['size'] ?? 0)
        );
    }
}
