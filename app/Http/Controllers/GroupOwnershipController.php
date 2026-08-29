<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupOwnershipTransfer;
use App\Models\User;
use App\Notifications\GroupOwnershipTransferRequested;
use App\Notifications\GroupOwnershipTransferResponded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupOwnershipController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Owner requests to hand off the group to another member. Nothing
     * changes yet — this just creates a pending request and notifies
     * the target. Roles only swap once they accept.
     */
    public function transfer(Request $request, Group $group)
    {
        $actor = $request->user();

        abort_unless($group->isAdmin($actor), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        abort_if((int) $validated['user_id'] === $actor->id, 422, 'You already own this group.');

        $target = User::findOrFail($validated['user_id']);

        abort_unless($group->isMember($target), 422, 'You can only transfer ownership to a current member.');

        abort_if(
            $group->pendingOwnershipTransfer()->exists(),
            422,
            'There is already a pending ownership transfer for this group. Cancel it before starting a new one.'
        );

        $transfer = GroupOwnershipTransfer::create([
            'group_id'     => $group->id,
            'from_user_id' => $actor->id,
            'to_user_id'   => $target->id,
            'status'       => 'pending',
        ]);

        $target->notify(new GroupOwnershipTransferRequested($transfer));

        return response()->json([
            'message' => __('Ownership transfer request sent to :name.', ['name' => $target->first_name]),
        ]);
    }

    /**
     * Owner cancels their own pending outgoing request.
     */
    public function cancel(Request $request, Group $group, GroupOwnershipTransfer $transfer)
    {
        $actor = $request->user();

        abort_unless($transfer->group_id === $group->id, 404);
        abort_unless($transfer->from_user_id === $actor->id, 403);
        abort_unless($transfer->status === 'pending', 422, 'This request has already been responded to.');

        $transfer->update(['status' => 'cancelled', 'responded_at' => now()]);

        return response()->json(['message' => __('Transfer request cancelled.')]);
    }

    /**
     * Recipient accepts. Recipient becomes admin, previous admin is
     * demoted to moderator (stays in the group, just isn't the owner).
     */
    public function accept(Request $request, GroupOwnershipTransfer $transfer)
    {
        $actor = $request->user();

        abort_unless($transfer->to_user_id === $actor->id, 403);
        abort_unless($transfer->status === 'pending', 422, 'This request is no longer pending.');

        $group = $transfer->group;

        DB::transaction(function () use ($group, $transfer) {
            $group->members()->where('user_id', $transfer->from_user_id)->update(['role' => 'moderator']);
            $group->members()->where('user_id', $transfer->to_user_id)->update(['role' => 'admin']);

            $transfer->update(['status' => 'accepted', 'responded_at' => now()]);
        });

        $transfer->fromUser->notify(new GroupOwnershipTransferResponded($transfer, accepted: true));

        return response()->json(['message' => __('You are now the owner of :name.', ['name' => $group->name])]);
    }

    /**
     * Recipient declines. Nothing changes except the original admin
     * gets notified their offer was turned down.
     */
    public function reject(Request $request, GroupOwnershipTransfer $transfer)
    {
        $actor = $request->user();

        abort_unless($transfer->to_user_id === $actor->id, 403);
        abort_unless($transfer->status === 'pending', 422, 'This request is no longer pending.');

        $transfer->update(['status' => 'rejected', 'responded_at' => now()]);

        $transfer->fromUser->notify(new GroupOwnershipTransferResponded($transfer, accepted: false));

        return response()->json(['message' => __('Transfer declined.')]);
    }
}