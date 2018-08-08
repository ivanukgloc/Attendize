<!-- sidebar menu: : style can be found in sidebar.less -->
<ul class="sidebar-menu" data-widget="tree">
    <li>
        <a href="{{ route('showSelectOrganiser') }}">
            <i class="fa fa-arrow-left"></i> <span>Back</span>
        </a>
    </li>
    <li class="header">MAIN NAVIGATION</li>
    <li>
        <a href="{{ route('admin::index') }}">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
    </li>
    <li class="{{ \App\Attendize\Utils::checkRoute(['admin::users.index', 'admin::users.create']) ? 'active': '' }}">
        <a href="{{ route('admin::users.index') }}">
            <i class="fa fa-user-secret"></i> <span>Users</span>
        </a>
    </li>
</ul>
