@if ($crud->hasAccess('impersonate') && backpack_user()->can("update",$entry))
   <a href="{{ url($crud->route.'/'.$entry->getKey().'/impersonate') }} " class="btn btn-sm btn-link {{ backpack_user()->id == $entry->getKey()? 'disabled': '' }}"><i class="fa fa-user"></i>Impersonate</a>
@endif

