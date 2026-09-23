<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('staff.nap-alerts', function (User $user) {
    return $user->isStaff();
});
