@if(backpack_user()->can("update",$entry))
<a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }} " class="btn btn-sm btn-link"><i class="la la-edit"></i>Edit</a>
@endif

