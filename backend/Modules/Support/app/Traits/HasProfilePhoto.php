<?php

namespace Modules\Support\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property string|null $profile_photo_url
 */
trait HasProfilePhoto
{
    /**
     * Boot profile photo handlers.
     */
    protected static function bootHasProfilePhoto(): void
    {
        static::deleting(function ($model) {
            if (!method_exists($model, 'isForceDeleting') || $model->isForceDeleting()) {
                $model->deleteProfilePhotoFile();
            }
        });
    }

    /**
     * Delete the current profile photo file from storage.
     *
     * @return void
     */
    public function deleteProfilePhotoFile(): void
    {
        $profilePhoto = $this->profile_photo_path;
        if (is_null($profilePhoto)) {
            return;
        }

        if (Storage::disk($profilePhoto['disk'])->exists($profilePhoto['path'])) {
            Storage::disk($profilePhoto['disk'])->delete($profilePhoto['path']);
        }
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return Attribute
     */
    public function profilePhotoUrl(): Attribute
    {
        return Attribute::make(get: function () {
            return isset($this->profile_photo_path['url'])
                ? $this->profile_photo_path['url']
                : $this->defaultProfilePhotoUrl(
                    $this->defaultProfilePhotoColor(),
                    $this->defaultProfilePhotoBackgroundColor()
                );
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @param string $color
     * @param string $background
     * @return string
     */
    public function defaultProfilePhotoUrl(string $color = "FFFFFF", string $background = "F57C00"): string
    {
        $name = trim(collect(explode(' ', $this->profilePhotoName()))->map(fn($segment) => mb_substr($segment, 0, 1))->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . "&color=$color&background=$background";
    }

    /**
     * Define the attribute the holds the model name
     *
     * @return string
     */
    public function profilePhotoName(): string
    {
        return $this->name;
    }

    /**
     * Default profile photo color
     *
     * @return string
     */
    public function defaultProfilePhotoColor(): string
    {
        return "FFFFFF";
    }

    /**
     * Default profile photo background color
     *
     * @return string
     */
    public function defaultProfilePhotoBackgroundColor(): string
    {
        return str_replace('#', '', setting('theme_primary_color', 'F57C00'));
    }

    /**
     * Replace the current profile photo with a new file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function replaceProfilePhoto(UploadedFile $file): void
    {
        $this->deleteProfilePhotoFile();
        $disk = $this->profilePhotoDisk();

        $path = $file->store($this->profilePhotoDirectory(), $disk);

        $this->updateQuietly([
            'profile_photo_path' => [
                'mime_type' => $file->getClientMimeType(),
                'name' => $file->getClientOriginalName(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => $disk,
                'extension' => $file->guessExtension() ?? $file->getClientOriginalExtension() ?? '',
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'url' => $path ? Storage::disk($disk)->url($path) : null,
            ]
        ]);
    }

    /**
     * Profile photo storage disk.
     *
     * @return string
     */
    protected function profilePhotoDisk(): string
    {
        return config('filesystems.default');
    }

    /**
     * Profile photo storage directory.
     *
     * @return string
     */
    protected function profilePhotoDirectory(): string
    {
        return $this->getTable() . '/profile-photos';
    }

    /**
     * Remove profile photo and clear related metadata.
     *
     * @return void
     */
    public function clearProfilePhoto(): void
    {
        $this->deleteProfilePhotoFile();
        $this->updateQuietly(['profile_photo_path' => null]);
    }
}
