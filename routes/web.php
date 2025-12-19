<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/password/reset/{token}', function ($token) {
    $email = request('email');
    return redirect("http://localhost:3000/auth/reset-password?token=$token&email=$email");
})->name('password.reset');