<?php

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/chat');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'users' => User::whereNot('id', 1)->get()
    ]);
})->name('dashboard');

Route::get('/chat', function () {
    return view('chat');
})->name('chat');

Route::get('/messages', function () {
    return ChatMessage::query()
       ->orderBy('id', 'asc')
       ->get();
});

Route::post('/messages', function () {
    $message = ChatMessage::create([
        'text' => request()->input('message')
    ]);

    broadcast(new MessageSent($message));
    return  $message;
});

Route::post('/messages/{id}', function ($id) {
    $message = ChatMessage::find($id)->delete();
    broadcast(new MessageSent($message));
    return  $message;
});

require __DIR__ . '/auth.php';
