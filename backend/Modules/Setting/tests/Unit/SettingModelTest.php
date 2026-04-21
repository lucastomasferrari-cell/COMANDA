<?php

namespace Modules\Setting\Tests\Unit;

use Illuminate\Encryption\Encrypter;
use Modules\Setting\Models\Setting;
use Tests\TestCase;

class SettingModelTest extends TestCase
{
    public function test_encryptable_payload_returns_null_when_app_key_is_missing(): void
    {
        $key = random_bytes(32);
        $encryptedPayload = (new Encrypter($key, 'AES-256-CBC'))->encrypt('secret-value');

        config(['app.key' => null]);
        app()->forgetInstance('encrypter');

        $setting = new Setting();
        $setting->setRawAttributes([
            'is_encryptable' => true,
            'payload' => $encryptedPayload,
        ]);

        $this->assertNull($setting->payload);
    }
}
