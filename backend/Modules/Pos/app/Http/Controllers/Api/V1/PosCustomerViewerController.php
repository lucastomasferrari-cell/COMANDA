<?php

namespace Modules\Pos\Http\Controllers\Api\V1;

use Modules\Core\Http\Controllers\Controller;
use Modules\Pos\Services\PosCustomerViewer\PosCustomerViewerServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PosCustomerViewerController extends Controller
{
    /**
     * Create a new instance of PosCustomerViewerController
     *
     * @param PosCustomerViewerServiceInterface $service
     */
    public function __construct(protected PosCustomerViewerServiceInterface $service)
    {
    }

    /**
     * Customer viewer stream
     *
     * @return StreamedResponse
     */
    public function stream(): StreamedResponse
    {
        set_time_limit(0);
        @ini_set('max_execution_time', '0');

        return response()->stream(function () {

            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');

            $lastFingerprint = null;

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $fingerprint = $this->service->fingerprint();
                if ($fingerprint !== $lastFingerprint) {

                    $snapshot = $this->service->snapshot();
                    echo "event: cart_snapshot\n";
                    echo "data: " . json_encode($snapshot, JSON_UNESCAPED_UNICODE) . "\n\n";

                    ob_flush();
                    flush();

                    $lastFingerprint = $fingerprint;
                }

                echo ": ping\n\n";
                ob_flush();
                flush();

                usleep(1000_000);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
