<?php

namespace Modules\Tool\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

class RestoreDatabaseRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:sql,txt|max:512000',
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return 'tool::attributes.database';
    }
}
