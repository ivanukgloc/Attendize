<?php

namespace App\Console\Commands;

use App\Notifications\TenantCreated;
use App\Tenant;
use Hyn\Tenancy\Models\Customer;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {business_name} {first_name} {last_name} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a tenant with the provided name and email address.';

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
     * @return mixed
     */

    public function handle()
    {
        $business_name = $this->argument('business_name');
        $first_name = $this->argument('first_name');
        $last_name = $this->argument('last_name');
        $email = $this->argument('email');

        if ($this->tenantExists($business_name, $email)) {
            $this->error("A tenant with name '{$business_name}' and/or '{$email}' already exists.");
            return;
        }

        $tenant = Tenant::createFrom($business_name, $first_name, $last_name, $email);
        $this->info("Tenant '{$business_name}' is created and is now accessible at {$tenant->hostname->fqdn}");

        // invite admin
        $tenant->admin->notify(new TenantCreated($tenant->hostname));
        $this->info("Admin {$email} has been invited!");
    }

    private function tenantExists($name, $email)
    {
        return Customer::where('name', $name)->orWhere('email', $email)->exists();
    }
}
