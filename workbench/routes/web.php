<?php

use Workbench\App\Jobs\ProcessPodcast;
use Workbench\App\Models\User;

Route::get('/test', function () {
    ProcessPodcast::dispatch();
    \Illuminate\Support\Facades\Auth::setUser(User::find(1));
    return response('Hello', 418);
});
