<?php

use Orchestra\Testbench\Factories\UserFactory;

Route::get('/test', function () {
    \Illuminate\Support\Facades\Auth::setUser((new UserFactory)->create());
    return response('Hello', 418);
});
