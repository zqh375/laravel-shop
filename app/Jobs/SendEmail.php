<?php


namespace App\Jobs;
use App\Models\Product;
use App\Notifications\InviteEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class SendEmail  implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle()
    {
        $emails=DB::table('admin_users')->select(['username','name'])->get();
        foreach ($emails as $email){
            $name=$email->name;
            $userMail=$email->username;
            Notification::route('mail', 'chenxi090418@qq.com')->notify(new InviteEmail($name));
        }
    }
}
