<?php

namespace Modules\GiftCard\Services\GiftCardCode;

use Modules\GiftCard\Models\GiftCard;

class GiftCardCodeService implements GiftCardCodeServiceInterface
{
    protected const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    protected const PREFIX = 'GC';
    protected const RANDOM_LENGTH = 11;
    protected const BATCH_PREFIX_LENGTH = 2;

    /** @inheritDoc */
    public function generate(?string $prefix = null): string
    {
        do {
            $normalizedPrefix = $this->normalizePrefix($prefix);
            $randomBody = $this->randomCharacters(static::RANDOM_LENGTH);
            $checksum = $this->generateChecksum(static::PREFIX . $normalizedPrefix . $randomBody);
            $payload = $randomBody . $checksum;

            $code = implode('-', array_filter([
                static::PREFIX,
                $normalizedPrefix ?: null,
                substr($payload, 0, 4),
                substr($payload, 4, 4),
                substr($payload, 8, 4),
            ]));
        } while (GiftCard::query()->withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /**
     * Reduce the optional batch prefix to a short unambiguous code segment.
     *
     * @param string|null $prefix
     * @return string
     */
    protected function normalizePrefix(?string $prefix): string
    {
        if (blank($prefix)) {
            return '';
        }

        return substr(
            implode('', array_filter(
                str_split($this->normalize($prefix)),
                fn(string $character) => str_contains(static::ALPHABET, $character)
            )),
            0,
            static::BATCH_PREFIX_LENGTH
        );
    }

    /** @inheritDoc */
    public function normalize(string $code): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($code)) ?: '';
    }

    /**
     * Generate random code characters from the safe cashier-entry alphabet.
     *
     * @param int $length
     * @return string
     */
    protected function randomCharacters(int $length): string
    {
        return collect(range(1, $length))
            ->map(fn() => static::ALPHABET[random_int(0, strlen(static::ALPHABET) - 1)])
            ->implode('');
    }

    /**
     * Generate a checksum character to make fabricated codes easier to reject.
     *
     * @param string $value
     * @return string
     */
    protected function generateChecksum(string $value): string
    {
        $alphabetLength = strlen(static::ALPHABET);
        $checksum = 0;

        foreach (str_split($value) as $index => $character) {
            $position = strpos(static::ALPHABET, $character);

            if ($position === false) {
                $position = ord($character) % $alphabetLength;
            }

            $checksum += ($index + 1) * ($position + 1);
        }

        return static::ALPHABET[$checksum % $alphabetLength];
    }
}
