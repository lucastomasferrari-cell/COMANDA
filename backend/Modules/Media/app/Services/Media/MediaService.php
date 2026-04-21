<?php

namespace Modules\Media\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Enum\MediaType;
use Modules\Media\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaService implements MediaServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("media::media.media");
    }

    /** @inheritDoc */
    public function get(array $filters = [], int $skip = 0): Collection
    {
        return $this->getModel()
            ->query()
            ->filters($filters)
            ->latest()
            ->take(30)
            ->skip($skip)
            ->get();
    }

    /** @inheritDoc */
    public function getModel(): Media
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Media::class;
    }

    /** @inheritDoc */
    public function store(UploadedFile $file, ?int $mediaId = null): ?Media
    {
        $path = Storage::putFile('media', $file);

        if ($path) {
            $object = [
                'disk' => config('filesystems.default'),
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'extension' => $file->guessExtension() ?? '',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                "type" => MediaType::File
            ];

            if ($mediaId) {
                $parent = Media::query()->findOrFail($mediaId);
                $media = new Media($object);
                $media->appendToNode($parent)->save();
            } else {
                $media = $this->getModel()->query()->create($object);
            }

            return $media;
        }

        return null;
    }

    /** @inheritDoc */
    public function update(int $id, string $name): Media
    {
        $media = $this->getModel()
            ->query()
            ->where("id", $id)
            ->firstOrFail();

        $media->update(["name" => $name]);

        return $media;
    }

    /** @inheritDoc */
    public function storeFolder(array $data): Media
    {
        $object = [
            'name' => $data['folder_name'],
            "type" => MediaType::Folder
        ];

        $folderId = $data['folder_id'] ?? null;
        if ($folderId) {
            $parent = Media::query()->findOrFail($folderId);
            $media = new Media($object);
            $media->appendToNode($parent)->save();
        } else {
            $media = $this->getModel()->query()->create($object);
        }
        
        return $media;
    }

    /** @inheritDoc */
    public function destroy(int|array|string $ids): bool
    {
        return count($this->getModel()->query()->find(parseIds($ids))->each->delete()) > 0;
    }

    /** @inheritDoc */
    public function downloadFile(int $id): StreamedResponse
    {
        $media = $this->getModel()
            ->query()
            ->where("id", $id)
            ->where("type", MediaType::File)
            ->firstOrFail();

        return $media->download();
    }
}
