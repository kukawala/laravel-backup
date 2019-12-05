<?php
namespace Kukawala\LaravelBackup\Src\Commands;

use Illuminate\Console\Command;
use App\Http\General\SettingOpration;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupCommands extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'jk:setup {fnc}{arg?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "list of commands run when any update for database while impliment new changes";



    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        if ($this->argument('fnc') != 'setBackUp') {
            $settingOpration = new SettingOpration();
            $settingOpration->updateConfigData();
        }

        $this->argument('fnc');
        if (method_exists($this, $this->argument('fnc'))) {
            $this->{$this->argument('fnc')}();
        }
    }

  

    /**
     * create command to flush database
     * @return [type] [description]
     */
    public function flushDataBase()
    {

        \DB::statement("TRUNCATE TABLE order_subscriptions");
        \DB::statement("TRUNCATE TABLE subscription_features");
        \DB::statement("TRUNCATE TABLE pets");
        \DB::statement("TRUNCATE TABLE payment_schedulers");
        \DB::statement("TRUNCATE TABLE orders");
        \DB::statement("TRUNCATE TABLE carts");
        \DB::statement("TRUNCATE TABLE subscription_usage_histories");
        \DB::statement("TRUNCATE TABLE subscribed_addons");
        \DB::statement("TRUNCATE TABLE change_plan_requests");
        \DB::statement("TRUNCATE TABLE authorize_transactions");
        \DB::statement("TRUNCATE TABLE authorize_transaction_payment_scheduler");
        \DB::statement("TRUNCATE TABLE mail_queues");
        \DB::statement("TRUNCATE TABLE failed_jobs");
        \DB::statement("TRUNCATE TABLE jobs");
        \DB::statement("TRUNCATE TABLE payment_refunds");
        \DB::statement("TRUNCATE TABLE payment_transactions");
        \DB::statement("TRUNCATE TABLE cage_bookings");
        \DB::statement("TRUNCATE TABLE cage_booking_logs");
        \DB::statement("TRUNCATE TABLE cage_booking_visits");


        //\DB::statement("TRUNCATE TABLE plans");
        //\DB::statement("TRUNCATE TABLE plan_levels");
        //\DB::statement("TRUNCATE TABLE features");
        //\DB::statement("TRUNCATE TABLE feature_plan_level");
        //\DB::statement("TRUNCATE TABLE addons");
        //\DB::statement("TRUNCATE TABLE addon_plan");

        if ($this->argument('arg') !== null && $this->argument('arg') == 'user') {
            \DB::statement("TRUNCATE TABLE users");
            \DB::statement("TRUNCATE TABLE user_credit_cards");
        }

        \DB::connection('audit')->statement('DELETE FROM `audit_requests` WHERE origin_model IN("PaymentScheduler","OrderSubscription")');
        \DB::connection('audit')->statement('DELETE FROM `audit_records` WHERE model IN("PaymentScheduler","OrderSubscription")');
    }

    /**
     * script to take backup of current system
     * Accepts argument of path where you want take backup
     * @return [type] [description]
     */
    public function takeBackUp()
    {

        $pro =  base_path();
        $backupDir = storage_path();

        /**
         * if path is passed from argument then it will use that file
         */
        if ($this->argument('arg') !== null) {
            $backupDir = $pro.'/'.$this->argument('arg');

            if (!is_dir($backupDir)) {
                exec('mkdir '.$pro.'/'.$this->argument('arg'));
            }
        }

        /**
         * create folder for backup
         */
        $backupFolder = $backupDir.'/wellness'.\Carbon\Carbon::now()->format('Y-m-d');
        exec('mkdir '.$backupFolder.';');

        /**
         * get all database which are configured with system
         */
        foreach (config('database.connections') as $key => $connection) {
            exec(sprintf(
                "mysqldump -u %s -p'%s' '%s' > %s",
                $connection['username'],
                $connection['password'],
                $connection['database'],
                $backupFolder.'/'.$key.'-database.sql'
            ));
        }

        /**
         * list of files which are need to ignore form backup
         */
        $excludeList= [
            $backupDir.'/**\*',
            '/admin/**\*',
            '/frontend/**\*',
            '/storage/**\*',
            '/\*.git',
            '/.git/**\ ',
            '*.git',
            '/public/tmp/**\*',
            '/public/reports/**\*',
            '/public/dist/**\*',
            '/public/dist_admin/**\*',
        ];


        $exclude = implode(' ', $excludeList);

        /**
         * go inside working directory to create zip without hierarchy structure
         */
        exec('cd '.$pro);

        /**
         * final create zip file
         */
        //exec('zip -r '.$backupFolder.'/source.zip ./* -x '.$exclude);
    }

    /**
     * restore backuped database by given path
     */
    public function setBackUp()
    {
    
        $path =  base_path().'/'.$this->argument('arg');

        if (!is_dir($path)) {
            exit('not a directory');
        }

        /**
         * get all database which are configured with system
         */
        foreach (config('database.connections') as $key => $connection) {
            if (file_exists($path.'/'.$key.'-database.sql')) {
                $restoreCommand = "mysql -u '".$connection['username']."' -p'".$connection['password']."' '".$connection['database']."' < ".$path."/".$key."-database.sql";


                exec($restoreCommand, $restoreResult, $result);

                if ($result == 0) {
                    echo "Database '{$key}' restored successfully from '{$path}/{$key}-database.sql'";
                } else {
                    echo "fail to resoter database {$key}";
                }
            } else {
                echo "File not found {$path}/{$key}-database.sql";
            }
        }
    }
}
