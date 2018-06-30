<?php

namespace App\Http\Controllers;

use App\Timezone;
use Artisan;
use Config;
use DB;
use Illuminate\Http\Request;

class InstallerController extends Controller
{

    /**
     * InstallerController constructor.
     */
    public function __construct()
    {
        /**
         * If we're already installed kill the request
         * @todo Check if DB is installed etc.
         */
        if (file_exists(base_path('installed'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Show the application installer
     *
     * @return mixed
     */
    public function showInstaller()
    {
        /*
         * Path we need to make sure are writable
         */
        $data['paths'] = [
            storage_path('app'),
            storage_path('framework'),
            storage_path('logs'),
            public_path(config('attendize.event_images_path')),
            public_path(config('attendize.organiser_images_path')),
            public_path(config('attendize.event_pdf_tickets_path')),
            base_path('bootstrap/cache'),
            base_path('.env'),
            base_path(),
        ];

        /*
         * Required PHP extensions
         */
        $data['requirements'] = [
            'openssl',
            'pdo',
            'mbstring',
            'fileinfo',
            'tokenizer',
            'gd',
            'zip',
        ];

        /*
         * Optional PHP extensions
         */
        $data['optional_requirements'] = [
            'pdo_pgsql',
            'pdo_mysql',
        ];

        return view('Installer.Installer', $data);
    }

    /**
     * Attempts to install the system
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|array
     */
    public function postInstaller(Request $request)
    {
        set_time_limit(300);

        $database['type'] = $request->get('database_type');
        $database['host'] = $request->get('database_host');
        $database['name'] = $request->get('database_name');
        $database['username'] = $request->get('database_username');
        $database['password'] = $request->get('database_password');

        $mail['driver'] = $request->get('mail_driver');
        $mail['port'] = $request->get('mail_port');
        $mail['username'] = $request->get('mail_username');
        $mail['password'] = $request->get('mail_password');
        $mail['encryption'] = $request->get('mail_encryption');
        $mail['from_address'] = $request->get('mail_from_address');
        $mail['from_name'] = $request->get('mail_from_name');
        $mail['host'] = $request->get('mail_host');

        $app_url = $request->get('app_url');
        $app_key = str_random(16);
        $version = file_get_contents(base_path('VERSION'));

        if ($request->get('test') === 'db') {
            $is_db_valid = self::testDatabase($database);

            if ($is_db_valid === 'yes') {
                return [
                    'status'  => 'success',
                    'message' => trans("Installer.connection_success"),
                    'test'    => 1,
                ];
            }

            return [
                'status'  => 'error',
                'message' => trans("Installer.connection_failure"),
                'test'    => 1,
            ];
        }

        $config_string = file_get_contents(base_path() . '/.env.example');
        $config_temp = explode("\n", $config_string);
        foreach($config_temp as $key=>$row)
            $config_temp[$key] = explode("=", $row, 2);
        $config = [
            "APP_ENV" => "production",
            "APP_DEBUG" => "false",
            "APP_URL" => $app_url,
            "APP_KEY" => $app_key,
            "DB_TYPE" => $database['type'],
            "DB_HOST" => $database['host'],
            "DB_DATABASE" => $database['name'],
            "DB_USERNAME" => $database['username'],
            "DB_PASSWORD" => $database['password'],
            "MAIL_DRIVER" => $mail['driver'],
            "MAIL_PORT" => $mail['port'],
            "MAIL_ENCRYPTION" => $mail['encryption'],
            "MAIL_HOST" => $mail['host'],
            "MAIL_USERNAME" => $mail['username'],
            "MAIL_FROM_NAME" => $mail['from_name'],
            "MAIL_FROM_ADDRESS" => $mail['from_address'],
            "MAIL_PASSWORD" => $mail['password'],
        ];
        foreach($config as $key=>$val) {
            $set = false;
            foreach($config_temp as $rownum=>$row) {
                if($row[0]==$key) {
                    $config_temp[$rownum][1] = $val;
                    $set = true;
                }
            }
            if(!$set)
                $config_temp[] = [$key, $val];
        }
        $config_string = "";
        foreach($config_temp as $row)
            if(count($row)>1)
                $config_string .= implode("=", $row)."\n";
            else
                $config_string .= implode("", $row)."\n";

        $fp = fopen(base_path() . '/.env', 'w');
        fwrite($fp, $config_string);
        fclose($fp);

        Config::set('database.default', $database['type']);
        Config::set("database.connections.{$database['type']}.host", $database['host']);
        Config::set("database.connections.{$database['type']}.database", $database['name']);
        Config::set("database.connections.{$database['type']}.username", $database['username']);
        Config::set("database.connections.{$database['type']}.password", $database['password']);

        DB::reconnect();

        //force laravel to regenerate a new key (see key:generate sources)
        Config::set('app.key', $app_key);
        Artisan::call('key:generate');
        Artisan::call('migrate', ['--force' => true]);
        if (Timezone::count() == 0) {
            Artisan::call('db:seed', ['--force' => true]);
        }
        Artisan::call('optimize', ['--force' => true]);

        $fp = fopen(base_path() . '/installed', 'w');
        fwrite($fp, $version);
        fclose($fp);

        return redirect()->route('showSignup', ['first_run' => 'yup']);
    }

    private function testDatabase($database)
    {
        Config::set('database.default', $database['type']);
        Config::set("database.connections.{$database['type']}.host", $database['host']);
        Config::set("database.connections.{$database['type']}.database", $database['name']);
        Config::set("database.connections.{$database['type']}.username", $database['username']);
        Config::set("database.connections.{$database['type']}.password", $database['password']);

        try {
            DB::reconnect();
            $success = DB::connection()->getDatabaseName() ? 'yes' : 'no';
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $success;
    }
}
