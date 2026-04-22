<?php

namespace App\Traits;

/**
 * Auto-asigna un color hex a la categoria cuando se crea sin color.
 * Paleta inspirada en Toast: 8 colores en orden circular basado en
 * el count de categorias existentes.
 *
 * @property string|null $color
 */
trait HasCategoryColor
{
    /** @var array<string> Paleta Toast. */
    private const COLOR_PALETTE = [
        '#1D9E75', // teal
        '#EF9F27', // amber
        '#378ADD', // blue
        '#D4537E', // pink
        '#7F77DD', // purple
        '#639922', // green
        '#5F5E5A', // gray
        '#D85A30', // coral
    ];

    public static function bootHasCategoryColor(): void
    {
        static::creating(function ($model) {
            if (empty($model->color)) {
                $count = static::query()->count();
                $model->color = self::COLOR_PALETTE[$count % count(self::COLOR_PALETTE)];
            }
        });
    }
}
