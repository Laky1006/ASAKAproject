<?php

// app/Http/Controllers/NotificationController.php
// lesons DONE
//providers
// reguser

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'type' => $note->type,
                    'service_title' => $note->service_title,
                    'reguser_name' => $note->reguser_name,
                    'date' => $note->date,
                    'time' => $note->time,
                    'created_at' => $note->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
            'auth' => [
                'user' => $user,
            ],
        ]);
    }
}
