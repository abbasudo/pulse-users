<?php

Route::get('/test', function () {
    return response('Hello', 418);
});
