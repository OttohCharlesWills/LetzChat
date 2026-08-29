<?php

namespace App\Notifications;

use App\Models\GroupOwnershipTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GroupOwnershipTransferRequested extends Notification
{
    use Queueable;

    public function __construct(public GroupOwnershipTransfer $transfer)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $transfer = $this->transfer->loadMissing(['group', 'fromUser']);
        $fromName = trim($transfer->fromUser->first_name . ' ' . $transfer->fromUser->last_name);

        return [
            'transfer_uuid'   => $transfer->uuid,
            'group_id'        => $transfer->group_id,
            'group_uuid'      => $transfer->group->uuid,
            'group_name'      => $transfer->group->name,
            'from_user_id'    => $transfer->from_user_id,
            'from_user_name'  => $fromName,
            'message'         => __(':name wants to make you the owner of :group.', [
                'name'  => $fromName,
                'group' => $transfer->group->name,
            ]),
        ];
    }
}