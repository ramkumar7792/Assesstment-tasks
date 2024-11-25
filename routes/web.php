<?php

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    return view('chat', [
        'friend' => Auth::user()
    ]);
})->middleware(['auth'])->name('dashboard');

Route::get('/items/{friend}', function (User $friend) {
    return ChatMessage::query()
        ->where(function ($query) use ($friend) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $friend->id);
        })
        ->orWhere(function ($query) use ($friend) {
            $query->where('sender_id', $friend->id)
                ->where('receiver_id', auth()->id());
        })
       ->with(['sender', 'receiver'])
       ->orderBy('id', 'asc')
       ->get();
})->middleware(['auth']);

Route::post('/items/{friend}', function (User $friend) {
    $item = ChatMessage::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $friend->id,
        'text' => request()->input('item')
    ]);

    broadcast(new MessageSent($item));
    return  $item;
});

Route::post('/items/{friend}/{id}', function (User $friend, $id) {
    $item = ChatMessage::find($id)->delete();
    broadcast(new MessageSent($item));
    return  $item;
});

require __DIR__ . '/auth.php';
