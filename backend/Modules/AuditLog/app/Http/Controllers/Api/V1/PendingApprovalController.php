<?php

namespace Modules\AuditLog\Http\Controllers\Api\V1;

use App\Forkiva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AuditLog\Models\PendingApproval;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;

class PendingApprovalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->input('status', 'pending');

        $paginator = PendingApproval::query()
            ->with(['user:id,name', 'reviewer:id,name'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(Forkiva::paginate())
            ->withQueryString();

        return ApiResponse::pagination(paginator: $paginator, resource: null);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        /** @var PendingApproval $approval */
        $approval = PendingApproval::findOrFail($id);
        abort_unless($approval->status === 'pending', 422, __('auditlog::pending_approvals.not_pending'));

        $approval->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_notes' => $data['notes'] ?? null,
        ]);

        return ApiResponse::success(body: $approval, message: __('auditlog::pending_approvals.approved'));
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'notes' => 'required|string|min:10|max:2000',
        ]);

        /** @var PendingApproval $approval */
        $approval = PendingApproval::findOrFail($id);
        abort_unless($approval->status === 'pending', 422, __('auditlog::pending_approvals.not_pending'));

        $approval->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_notes' => $data['notes'],
        ]);

        return ApiResponse::success(body: $approval, message: __('auditlog::pending_approvals.rejected'));
    }
}
