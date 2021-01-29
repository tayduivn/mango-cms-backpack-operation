<!-- This file is used to store topbar (left) items -->

{{-- <li class="nav-item px-3"><a class="nav-link" href="#">Dashboard</a></li>
<li class="nav-item px-3"><a class="nav-link" href="#">Users</a></li>
<li class="nav-item px-3"><a class="nav-link" href="#">Settings</a></li> --}}
@if (backpack_user()->isImpersonating())
    <li><a href="{{ url('admin/stop-impersonating') }}">Stop Impersonating</a></li>
@endif
