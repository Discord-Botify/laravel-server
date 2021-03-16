<?php


namespace App\Http\Controllers;


use App\Models\AppSession;
use App\Models\Notification;
use Exception;
use Illuminate\Routing\Controller as BaseController;


class NotificationController extends BaseController
{
    public function unDismissed()
    {
        $notifications = Notification::select([
            'notification_id',
            'artist_name',
            'album_type',
            'album_name',
            'album_uri',
            'created_at'
        ])
            ->unDismissed()
            ->orderBy('created_at')
            ->get()
            ->keyBy('notification_id');

        return $notifications;
    }

    public function dismiss(string $notification_id)
    {
        Notification::where('notification_id', $notification_id)
            ->where('user_id_to', AppSession::id())
            ->update(['notification_dismissed' => 1]);

        return response('Dismissed', 200);
    }

    public function dismissAll()
    {
        Notification::where('user_id_to', AppSession::id())
            ->update(['notification_dismissed' => 1]);

        return response('Dismissed all notifications', 200);
    }
}
