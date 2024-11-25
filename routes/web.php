<?php

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/chat/1/');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'users' => User::whereNot('id', 1)->get()
    ]);
})->name('dashboard');

Route::get('/chat/{friend}', function (User $friend) {
    return view('chat', [
        'friend' => $friend
    ]);
})->name('chat');

Route::get('/messages', function () {
    return ChatMessage::query()
        ->where(function ($query){
            $query->where('sender_id', 1)
                ->where('receiver_id', 1);
        })
        ->orWhere(function ($query) {
            $query->where('sender_id', 1)
                ->where('receiver_id', 1);
        })
       ->with(['sender', 'receiver'])
       ->orderBy('id', 'asc')
       ->get();
});

Route::post('/messages', function () {
    $message = ChatMessage::create([
        'sender_id' => 1,
        'receiver_id' => 1,
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
