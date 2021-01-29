<?php

namespace CompanyName\ModerateOperation\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\UserStoreCrudRequest as StoreRequest;
use App\Http\Requests\UserUpdateCrudRequest as UpdateRequest;
use App\Http\Controllers\Admin\Operations\ImpersonateOperation;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Illuminate\Support\Facades\Hash;

Trait UserCrudControllerOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use ShowOperation;
    use ImpersonateOperation;

    public function setup()
    {
        $this->crud->setModel(config('backpack.permissionmanager.models.user'));
        $this->crud->setEntityNameStrings(trans('backpack::permissionmanager.user'), trans('backpack::permissionmanager.users'));
        $this->crud->setRoute(backpack_url('user'));
        if(!backpack_user()->hasRole("Admin")){
            $this->crud->denyAccess("create");
            $this->crud->denyAccess("delete");
        }
    }

    public function setupListOperation()
    {
        $this->crud->addButton('line', 'update', 'view', 'crud::buttons.edit',"beginning");
        $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete');
        $this->crud->addColumns([
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => trans('backpack::permissionmanager.email'),
                'type'  => 'email',
            ],
            [ // n-n relationship (with pivot table)
                'label'     => trans('backpack::permissionmanager.roles'), // Table column heading
                'type'      => 'select_multiple',
                'name'      => 'roles', // the method that defines the relationship in your Model
                'entity'    => 'roles', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => config('permission.models.role'), // foreign key model
            ],
            [ // n-n relationship (with pivot table)
                'label'     => trans('backpack::permissionmanager.extra_permissions'), // Table column heading
                'type'      => 'select_multiple',
                'name'      => 'permissions', // the method that defines the relationship in your Model
                'entity'    => 'permissions', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => config('permission.models.permission'), // foreign key model
            ],
        ]);

        // Role Filter
        $this->crud->addFilter(
            [
                'name'  => 'role',
                'type'  => 'dropdown',
                'label' => trans('backpack::permissionmanager.role'),
            ],
            config('permission.models.role')::all()->pluck('name', 'id')->toArray(),
            function ($value) { // if the filter is active
                $this->crud->addClause('whereHas', 'roles', function ($query) use ($value) {
                    $query->where('role_id', '=', $value);
                });
            }
        );

        // Extra Permission Filter
        $this->crud->addFilter(
            [
                'name'  => 'permissions',
                'type'  => 'select2',
                'label' => trans('backpack::permissionmanager.extra_permissions'),
            ],
            config('permission.models.permission')::all()->pluck('name', 'id')->toArray(),
            function ($value) { // if the filter is active
                $this->crud->addClause('whereHas', 'permissions', function ($query) use ($value) {
                    $query->where('permission_id', '=', $value);
                });
            }
        );
    }
    public function setupShowOperation(){
        $this->setupListOperation();
    }
    public function setupCreateOperation()
    {
        $this->addUserFields();
        $this->crud->setValidation(StoreRequest::class);
    }

    public function setupUpdateOperation()
    {
        $user = User::where("id",$this->crud->getCurrentEntryId())->first();
        if(backpack_user()->can("update",$user)) {
            $this->addUserFields();
            $this->crud->setValidation(UpdateRequest::class);
        }else{
            abort(403);
        }
    }

    /**
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitStore();
    }

    /**
     * Update the specified resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }

    /**
     * Handle password input fields.
     */
    protected function handlePasswordInput($request)
    {
        // Remove fields not present on the user.
        $request->request->remove('password_confirmation');
        $request->request->remove('roles_show');
        $request->request->remove('permissions_show');

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        return $request;
    }

    protected function addUserFields()
    {
        $this->crud->addFields([
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => trans('backpack::permissionmanager.email'),
                'type'  => 'email',
            ],
            [
                'name'  => 'password',
                'label' => trans('backpack::permissionmanager.password'),
                'type'  => 'password',
            ],
            [
                'name'  => 'password_confirmation',
                'label' => trans('backpack::permissionmanager.password_confirmation'),
                'type'  => 'password',
            ],
            ]);
        if(backpack_user()->hasRole("Admin")){
            $this->crud->addFields([
                [
                    // two interconnected entities
                    'label'             => trans('backpack::permissionmanager.user_role_permission'),
                    'field_unique_name' => 'user_role_permission',
                    'type'              => 'checklist_dependency',
                    'name'              => ['roles', 'permissions'],
                    'subfields'         => [
                        'primary' => [
                            'label'            => trans('backpack::permissionmanager.roles'),
                            'name'             => 'roles', // the method that defines the relationship in your Model
                            'entity'           => 'roles', // the method that defines the relationship in your Model
                            'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
                            'attribute'        => 'name', // foreign key attribute that is shown to user
                            'model'            => config('permission.models.role'), // foreign key model
                            'pivot'            => true, // on create&update, do you need to add/delete pivot table entries?]
                            'number_columns'   => 3, //can be 1,2,3,4,6
                        ],
                        'secondary' => [
                            'label'          => ucfirst(trans('backpack::permissionmanager.permission_singular')),
                            'name'           => 'permissions', // the method that defines the relationship in your Model
                            'entity'         => 'permissions', // the method that defines the relationship in your Model
                            'entity_primary' => 'roles', // the method that defines the relationship in your Model
                            'attribute'      => 'name', // foreign key attribute that is shown to user
                            'model'          => config('permission.models.permission'), // foreign key model
                            'pivot'          => true, // on create&update, do you need to add/delete pivot table entries?]
                            'number_columns' => 3, //can be 1,2,3,4,6
                        ],
                    ],
                ],
            ]);
        }
    }
    public function destroy($id)
    {
        $user = User::find($id);
        if(!backpack_user()->can("delete",$user)){
            abort(403);
        }
        $this->crud->hasAccessOrFail('delete');

        return $this->crud->delete($id);
    }
}
