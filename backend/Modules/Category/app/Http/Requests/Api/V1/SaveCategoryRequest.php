<?php

namespace Modules\Category\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

/**
 * @property-read int $menu_id
 * @property-read string|null $slug
 */
class SaveCategoryRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            ...$this->getTranslationRules(['name' => "required|string|max:255"]),
            ...$this->getMenuRule(),
            "sku" => [
                'bail',
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'sku')->ignore($this->route('id')),
            ],
            // Slug ahora es nullable — el Category model auto-genera desde
            // name en el creating hook si viene vacío. Si el caller envía
            // uno manual, validamos formato + unicidad. Permitir vacío en
            // el body hace que el form de admin pueda omitir el campo.
            "slug" => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')
                    ->where('menu_id', $this->menu_id)
                    ->ignore($this->route('id')),
            ],
            "parent_id" => "bail|nullable|numeric|exists:categories,id,deleted_at,NULL,menu_id,$this->menu_id",
            "is_active" => "required|boolean",
            // Color hex #RRGGBB. Nullable — si viene vacio, el trait
            // HasCategoryColor auto-asigna desde la paleta Toast al save.
            "color" => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            // color_hue (0-360): Sprint 1.B — se usa para generar placeholders
            // de producto sin foto. El admin elige un hue con slider simple
            // y saturación/luminosidad las fija el frontend para consistencia.
            "color_hue" => ['nullable', 'integer', 'min:0', 'max:360'],
        ];
    }

    /** @inheritDoc */
    public function validationData(): array
    {
        return [
            ...parent::validationData(),
            'slug' => $this->slug ? str($this->slug)->slug()->toString() : null,
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "category::attributes.categories";
    }
}
