<?php

namespace Different\PatentOAuthClient\app\Console\Commands;

use App\Models\User;
use Different\PatentOAuthClient\PatentOAuthClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PasUserBatchSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pas:users-batch-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all users with PAS, warning: passwords will be lost';

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
        $this->info('Szinkronizáció kezdése...');
        $this->batchSyncUsers();
        $this->info('Szinkronizáció vége.');
        return 0;
    }

    public function batchSyncUsers()
    {
        $users = User::query()
            ->whereNull('pas_id')
            ->get();

        if($users->count() <= 0)
        {
            $this->warn('Nincsenek szinkronizálandó felhasználók.');
            return;
        }

        $users->each(function (User $user){
             //$password = Str::random(20);
            $password = '12345678';

             $pas_user = PatentOAuthClient::handlePostUser(
                 $user->email,
                 $user->name,
                 $password,
                 $user->id,
             );

            if(!$pas_user) {
                $this->error($user->name_email . ' szinkronizációja nem sikerült.');
                return true;
            }

            $user->password = bcrypt($password);
            $user->pas_id = $pas_user['id'];
            $user->save();

            $this->info($user->name_email . ' szinkronizációja sikerült.');
        });
    }
}
