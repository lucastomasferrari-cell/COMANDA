<?php

namespace Modules\Pos\Listeners;

use Modules\Pos\Events\OpenPosSession;
use Modules\Pos\Models\PosRegister;

class UpdateRegisterLastSession
{
    /**
     * Handle the event.
     */
    public function handle(OpenPosSession $event): void
    {
        PosRegister::query()
            ->withOutGlobalBranchPermission()
            ->where('id', $event->session->pos_register_id)
            ->update(['last_session_id' => $event->session->id]);
    }
}
