<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Discord\Discord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
     * @var int
     */
    private $notifications_sent;
    /**
     * @var int
     */
    private $total_notifications;

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
        $this->notifications_sent = 0;

        $discord = new Discord([
            'token' => env('DISCORD_BOT_TOKEN'),
            'pmChannels' => true,
        ]);

        $discord->on('ready', function(Discord $discord)
        {
            $this->sendMessages($discord);
        });
        $discord->run();

        while (true) {
            sleep(1);
        }

    }

    protected function sendMessages(Discord $discord)
    {
        // TODO implement notification types based on user selection of preferred notification method
        // Get all users with pending notifications who are registered with Discord
        $users = User::with('discord_notifications')
            ->whereHas('discord_notifications')
            ->discordUser()
            ->get();

        if ($users->isEmpty())
        {
            exit();
        }

        // Determine the total message count
        $this->total_notifications = $users->reduce(function ($carry, $user)
        {
            return $carry + $user->discord_notifications->count();
        });

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
                $discord_user->sendMessage($message)->done(function ($message) use ($users, $notification)
                {
                    // After each message is sent, update the global message counter
                    $this->notifications_sent++;

                    // Mark the notification as sent
                    $notification->notification_sent = 1;
                    $notification->save();

                    // When we hit the message limit, end the script
                    if ($this->notifications_sent >= $this->total_notifications)
                    {
                        Log::info('Sent ' . $this->total_notifications . ' Discord notifications to ' . $users->count() . ' users');
                        exit();
                    }
                });
            }
        }
    }
}
