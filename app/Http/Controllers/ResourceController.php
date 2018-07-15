<?php

namespace App\Traits\Controllers;

use Illuminate\Http\Request;
use App\Tenant;
use App\User;
use App\Account;
use App\Notifications\TenantCreated;
use Illuminate\Support\Facades\Hash;
use Hyn\Tenancy\Contracts\Repositories\CustomerRepository;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Customer;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;

trait ResourceController
{
    use ResourceHelper;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $this->authorize('viewList', $this->getResourceModel());

        $paginatorData = [];
        $perPage = (int) $request->input('per_page', '');
        $perPage = (is_numeric($perPage) && $perPage > 0 && $perPage <= 100) ? $perPage : 15;
        if ($perPage != 15) {
            $paginatorData['per_page'] = $perPage;
        }
        $search = trim($request->input('search', ''));
        if (! empty($search)) {
            $paginatorData['search'] = $search;
        }
        $records = $this->getSearchRecords($request, $perPage, $search);
        $records->appends($paginatorData);

        return view('_resources.index', $this->filterSearchViewData($request, [
            'records' => $records,
            'search' => $search,
            'resourceAlias' => $this->getResourceAlias(),
            'resourceRoutesAlias' => $this->getResourceRoutesAlias(),
            'resourceTitle' => $this->getResourceTitle(),
            'perPage' => $perPage,
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $this->authorize('create', $this->getResourceModel());

        $class = $this->getResourceModel();
        return view('_resources.create', $this->filterCreateViewData([
            'record' => new $class(),
            'resourceAlias' => $this->getResourceAlias(),
            'resourceRoutesAlias' => $this->getResourceRoutesAlias(),
            'resourceTitle' => $this->getResourceTitle(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // $this->authorize('create', $this->getResourceModel());

        $valuesToSave = $this->getValuesToSave($request);
        $request->merge($valuesToSave);
        $this->resourceValidate($request, 'store');

        if (Customer::where('name', $valuesToSave['first_name'])->orWhere('email', $valuesToSave['email'])->exists()) {
            flash()->info("A tenant with name '{$valuesToSave['first_name']}' and/or '{$valuesToSave['email']}' already exists.");
            return;
        }

        $customer = new Customer;
        $customer->name = $valuesToSave['first_name'];
        $customer->email = $valuesToSave['email'];
        app(CustomerRepository::class)->create($customer);

        // associate the customer with a website
        $website = new Website;
        $website->customer()->associate($customer);
        app(WebsiteRepository::class)->create($website);

        // associate the website with a hostname
        $hostname = new Hostname;
        $baseUrl = config('app.url_base');
        $hostname->fqdn = "{$valuesToSave['first_name']}.{$baseUrl}";
        $hostname->customer()->associate($customer);
        app(HostnameRepository::class)->attach($hostname, $website);

        // make hostname current
        app(Environment::class)->hostname($hostname);
        
        $account = new Account();
        $account->first_name = $valuesToSave['first_name'];
        $account->last_name = $valuesToSave['last_name'];
        $account->email = $valuesToSave['email'];
        $account->currency_id = $valuesToSave['currency_id'];
        $account->timezone_id = $valuesToSave['timezone_id'];
        $account->save();

        $admin = new User();
        $admin->first_name = $valuesToSave['first_name'];
        $admin->last_name = $valuesToSave['last_name'];
        $admin->logo_number = $valuesToSave['logo_number'];
        $admin->email = $valuesToSave['email'];
        $admin->password = Hash::make(str_random());
        $admin->account_id = $account->id;
        $admin->is_parent = 1;
        $admin->is_registered = 1;
        $admin->save();

        $tenant = new Tenant($customer, $website, $hostname, $admin);

        // $tenant = Tenant::createFrom($valuesToSave['name'], $valuesToSave['email']);
        // flash()->info("Tenant '{$valuesToSave['name']}' is created and is now accessible at {$tenant->hostname->fqdn}");

        // invite admin
        $tenant->admin->notify(new TenantCreated($tenant->hostname));
        flash()->info("Admin {$valuesToSave['email']} has been invited!");

        return redirect(route('admin::users.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect(route($this->getResourceRoutesAlias().'.edit', $id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($id)
    {
        $record = $this->getResourceModel()::findOrFail($id);

        // $this->authorize('update', $record);

        return view('_resources.edit', $this->filterEditViewData($record, [
            'record' => $record,
            'resourceAlias' => $this->getResourceAlias(),
            'resourceRoutesAlias' => $this->getResourceRoutesAlias(),
            'resourceTitle' => $this->getResourceTitle(),
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $record = $this->getResourceModel()::findOrFail($id);

        // $this->authorize('update', $record);

        $valuesToSave = $this->getValuesToSave($request, $record);
        $request->merge($valuesToSave);
        $this->resourceValidate($request, 'update', $record);

        if ($record->update($this->alterValuesToSave($request, $valuesToSave))) {
            flash()->success('Element successfully updated.');

            return $this->getRedirectAfterSave($record);
        } else {
            flash()->info('Element was not updated.');
        }

        return redirect(route($this->getResourceRoutesAlias().'.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        if ($customer = Customer::where('id', $id)->with(['websites', 'hostnames'])->firstOrFail()) {
            $hostname = $customer->hostnames->first();
            $website = $customer->websites->first();
            app(HostnameRepository::class)->delete($hostname, true);
            app(WebsiteRepository::class)->delete($website, true);
            app(CustomerRepository::class)->delete($customer, true);
            flash()->info("Tenant {$id} successfully deleted.");
        }

        return redirect(route('admin::users.index'));
    }
}
