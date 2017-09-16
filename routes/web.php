<?php

Route::get('/', "VideoRoomsController@index");
Route::prefix('room')->middleware('auth')->group(function() {
    Route::get('join/{roomName}', 'VideoRoomsController@joinRoom');
    Route::post('create', 'VideoRoomsController@createRoom');
});

Route::get('logout', 'Auth\LoginController@logout');

Auth::routes();
