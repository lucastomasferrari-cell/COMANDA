<?php

namespace Modules\Media\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;
use Modules\Media\Enum\MediaMime;
use Modules\Media\Enum\MediaType;
use Modules\Media\MediaHelper;

class UploadMediaRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'file' => "file||mimes:" . MediaMime::forValidation() . "|max:" . MediaHelper::$maxFileSize,
            "folder_id" => "bail|nullable|numeric:|exists:media,id,created_by,$user->id,type," . MediaType::Folder->value,
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "media::attributes.upload";
    }
}
