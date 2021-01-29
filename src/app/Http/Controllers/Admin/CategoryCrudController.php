<?php

namespace CompanyName\ModerateOperation\app\Http\Controllers\Admin;

use App\Models\Category;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Http\Requests\CategoryRequest;

Trait CategoryCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;

    public function setup()
    {
        CRUD::setModel("App\Models\Category");
        CRUD::setRoute(config('backpack.base.route_prefix', 'admin').'/category');
        CRUD::setEntityNameStrings('category', 'categories');
        if(backpack_user()->hasRole("User")){
            $this->crud->denyAccess("create");
            $this->crud->denyAccess("Update");
            $this->crud->denyAccess("delete");
        }
    }

    protected function setupListOperation()
    {
             $this->crud->addButton('line', 'update', 'view', 'crud::buttons.edit',"beginning");
             $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete');
        CRUD::addColumn('name');
        CRUD::addColumn('slug');
        CRUD::addColumn('parent');
    }
    protected function setupShowOperation()
    {
        $this->crud->addButton('line', 'update', 'view', 'crud::buttons.edit',"beginning");
        $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete');
        $this->setupListOperation();
        CRUD::addColumn([
            "name"=>"user_id",
            'type'=> 'select',
            "label"=>"Author",
            'entity' => "User",
            'attribute' => 'name',
            'wrapper'   => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('user/'.$entry->user_id.'/show');
                },
            ],
            'model' => "App\Models\User",
        ]);
        CRUD::addColumn('created_at');
        CRUD::addColumn('updated_at');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(CategoryRequest::class);

        CRUD::addField([
            "label"=>"User",
            "name"=>"user_id",
            "type"=>"hidden",
            "default"=>backpack_user()->id
        ]);
        CRUD::addField([
            'name' => 'name',
            'label' => 'Name',
        ]);
        CRUD::addField([
            'name' => 'slug',
            'label' => 'Slug (URL)',
            'type' => 'text',
            'hint' => 'Will be automatically generated from your name, if left empty.',
            // 'disabled' => 'disabled'
        ]);
        CRUD::addField([
            'label' => 'Parent',
            'type' => 'select',
            'name' => 'parent_id',
            'entity' => 'parent',
            'attribute' => 'name',
        ]);

    }

    protected function setupUpdateOperation()
    {
        $category = Category::where("id",$this->crud->getCurrentEntryId())->first();
        if(!backpack_user()->can("update",$category)){
            abort(403);
        }
        $this->setupCreateOperation();
    }

    protected function setupReorderOperation()
    {
        CRUD::set('reorder.label', 'name');
        CRUD::set('reorder.max_level', 2);
    }
}
