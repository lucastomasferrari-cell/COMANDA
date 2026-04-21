<?php

namespace Modules\Support\app\Traits;

use DB;
use Throwable;

trait HasGeneratedCode
{
    /**
     * The status key for code.
     *
     * @var string
     */
    const CODE_COLUMN_NAME = 'code';

    /**
     * Boot HasGeneratedCode trait
     *
     * @return void
     * @throws Throwable
     */
    public static function bootHasGeneratedCode(): void
    {
        static::creating(function ($model) {
            if (is_null($model->{self::CODE_COLUMN_NAME})) {
                $model->{self::CODE_COLUMN_NAME} = $model->generateCode();
            }
        });
    }

    /**
     * Generate a unique code for a model with a prefix based on its class name.
     **
     * @return string
     * @throws Throwable
     */
    public function generateCode(): string
    {
        $table = $this->getTable();
        $prefix = $this->getGenerateCodePrefix();
        $separator = $this->getGenerateCodeSeparatorPrefix();

        $lastCode = DB::table($table)
            ->where('code', 'like', $prefix . $separator . '%')
            ->orderByDesc('id')
            ->value('code');

        if ($lastCode && preg_match('/' . $separator . '(\d+)$/', $lastCode, $matches)) {
            $next = (int)$matches[1] + 1;
        } else {
            $next = 1;
        }

        return sprintf("%s$separator%05d", $prefix, $next);
    }

    /**
     * Get generate code prefix
     *
     * @return string
     */
    public function getGenerateCodePrefix(): string
    {
        return strtoupper(substr(class_basename($this), 0, 2));
    }

    /**
     * Get generate code separator prefix
     *
     * @return string
     */
    public function getGenerateCodeSeparatorPrefix(): string
    {
        return "-";
    }
}
