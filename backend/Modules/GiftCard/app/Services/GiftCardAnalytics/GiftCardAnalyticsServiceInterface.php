<?php

namespace Modules\GiftCard\Services\GiftCardAnalytics;

interface GiftCardAnalyticsServiceInterface
{
    /**
     * Return filter schema and defaults for gift card analytics.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function filters(array $filters = []): array;

    /**
     * Build the full analytics payload.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function analytics(array $filters = []): array;

    /**
     * Build one analytics section payload.
     *
     * @param string $section
     * @param array<string,mixed> $filters
     * @return array<string,mixed>|array<int,mixed>
     */
    public function section(string $section, array $filters = []): array;
}
