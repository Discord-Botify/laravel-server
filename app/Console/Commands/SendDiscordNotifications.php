<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Discord\Discord;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendDiscordNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send_discord_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send queued Discord notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $discord = new Discord([
            'token' => env('DISCORD_BOT_TOKEN'),
            'pmChannels' => true,
        ]);

        $discord->on('ready', function(Discord $discord)
        {
            $this->sendMessages($discord);

        });
        $discord->run();

    }

    protected function sendMessages(Discord $discord)
    {
        // TODO implement notification types based on user selection of preferred notification method
        // Get all users with pending notifications
        $users = User::with('discord_notifications')
            ->whereHas('discord_notifications')
        ->get();

        foreach ($users as $user)
        {
            $discord_user = new \Discord\Parts\User\User($discord);

            $discord_user->id = $user->discord_id;

            foreach ($user->discord_notifications as $notification)
            {
                $album_type = $notification->album_type;
                $artist = $notification->artist_name;
                $album = $notification->album_name;
                $album_id = Str::afterLast($notification->album_uri, ':');
                $link = "https://open.spotify.com/album/$album_id";
                $message = "New $album_type from $artist: $album \n$link";
                $discord_user->sendMessage($message);

                // Mark the notification as sent
                $notification->notification_sent = 1;
                $notification->save();
            }
        }
    }
}
