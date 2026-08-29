<?php

namespace App\Notifications;

use App\Models\GroupOwnershipTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GroupOwnershipTransferResponded extends Notification
{
    use Queueable;

    public function __construct(
        public GroupOwnershipTransfer $transfer,
        public bool $accepted
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $transfer = $this->transfer->loadMissing(['group', 'toUser']);
        $toName = trim($transfer->toUser->first_name . ' ' . $transfer->toUser->last_name);

        return [
            'transfer_uuid' => $transfer->uuid,
            'group_id'      => $transfer->group_id,
            'group_uuid'    => $transfer->group->uuid,
            'group_name'    => $transfer->group->name,
            'to_user_id'    => $transfer->to_user_id,
            'to_user_name'  => $toName,
            'accepted'      => $this->accepted,
            'message'       => $this->accepted
                ? __(':name accepted your ownership transfer of :group. They are now the owner.', ['name' => $toName, 'group' => $transfer->group->name])
                : __(':name declined your ownership transfer of :group.', ['name' => $toName, 'group' => $transfer->group->name]),
        ];
    }
}