<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reguser;
use Illuminate\Support\Facades\Mail;
use App\Mail\InactiveUserMail;
use Carbon\Carbon;

class NotifyInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notify:inactive-users';

    /**
     * The console command description.
     */
    protected $description = 'Send an email to users who haven\'t booked anything recently.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $threshold = Carbon::now()->subDays(30);

        $inactiveRegusers = Reguser::where(function ($q) use ($threshold) {
            $q->whereNull('last_booking_at')
              ->orWhere('last_booking_at', '<', $threshold);
        })->with('user')->get();

        if ($inactiveRegusers->isEmpty()) {
            $this->info('No inactive users found.');
            return;
        }

        foreach ($inactiveRegusers as $reguser) {
            if ($reguser->user && $reguser->user->email) {
                Mail::to($reguser->user->email)->send(new InactiveUserMail($reguser));
                $this->info("Sent email to: {$reguser->user->email}");
            }
        }

        $this->info('Inactive user notifications sent successfully.');
    }
}
