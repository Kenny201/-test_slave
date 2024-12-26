<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('rows.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
