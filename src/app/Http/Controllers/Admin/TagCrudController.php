<?php

namespace CompanyName\ModerateOperation\app\Http\Controllers\Admin;

use App\Models\Tag;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use App\Http\Requests\TagRequest;
use CRUD;

Trait TagCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;

    public function setup()
    {
        $this->crud->setModel("App\Models\Tag");
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/tag');
        $this->crud->setEntityNameStrings('tag', 'tags');
        $this->setupListOperation();
        if(backpack_user()->hasRole("User")){
            $this->crud->denyAccess("create");
            $this->crud->denyAccess("update");
            $this->crud->denyAccess("delete");
        }
    }
    protected function setupListOperation(){
            $this->crud->addButton('line', 'update', 'view', 'crud::buttons.edit',"beginning");
            $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete');
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
        CRUD::addColumn("name");
        CRUD::addColumn("slug");
        CRUD::addColumn('created_at');
        CRUD::addColumn('updated_at');
    }
    protected function setupShowOperation(){
        $this->crud->addButton('line', 'update', 'view', 'crud::buttons.edit',"beginning");
        $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete');
        $this->setupListOperation();

    }
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(TagRequest::class);
        CRUD::addField([
            "label"=>"User",
            "name"=>"user_id",
            "type"=>"hidden",
            "default"=>backpack_user()->id
        ]);
        CRUD::setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $tag = Tag::where("id",$this->crud->getCurrentEntryId())->first();
        if(backpack_user()->cannot("update",$tag)){
            abort(403);
        }
        $this->crud->setValidation(TagRequest::class);
    }
}
