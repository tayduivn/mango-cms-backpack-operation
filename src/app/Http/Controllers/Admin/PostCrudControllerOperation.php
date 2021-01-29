<?php

namespace  CompanyName\ModerateOperation\app\Http\Controllers\Admin;

use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
/**
 * Class PostCrudControllerOperation
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
Trait PostCrudControllerOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;
    use CloneOperation;
    use \Backpack\ReviseOperation\ReviseOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;



    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Post::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/post');
        CRUD::setEntityNameStrings('post', 'posts');
        $this->crud->allowAccess('revisions');
        if(backpack_user()->hasRole("User")){
            $this->crud->denyAccess("create");
            $this->crud->denyAccess("update");
            $this->crud->denyAccess("delete");
        }
        if(!backpack_user()->hasRole("Admin")){
            $this->crud->denyAccess("clone");
            $this->crud->denyAccess("bulkClone");
            $this->crud->denyAccess("bulkDelete");
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->setActionsColumnPriority(10000);
                $this->crud->addButton('line', 'update', 'view', 'crud::buttons.edit');
                $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete');
        CRUD::addColumn([
            "name"=>"title",
            "label"=>"Title",
            "type"=>"text",
            "limit"=>30
        ]);
        CRUD::addColumn([
            'name'  => 'format_id',
            'label' => 'Format',
            'type'  => 'radio',
            // optionally override the Yes/No texts
            'options' => [
                1 => "Standard",
                2 => "Aside",
                3 => "Image",
                4 => "Video",
                5 => "Audio",
                6 => "Quote",
                7 => "Link",
                8 => "Gallery",
            ]
        ]);
        $this->crud->addColumn([
            'name' => 'image', // The db column name
            'label' => "Post Image", // Table column heading
            'type' => 'image',
            "disk"         => $this->crud->getCurrentEntry(),
            "upload"       =>true,
            'height' => '150px',
            'width'  => '130px'
        ]);
        $this->crud->addFilter(
            [
                'name'  => 'category',
                'type'  => 'select2_ajax',
                'label' => "Category",
                'placeholder' => 'Pick a category'
            ],
            url('admin/posts/ajax-category-options'), // the ajax route
            function ($value) { // if the filter is active
                $this->crud->addClause('whereHas', 'category', function ($query) use ($value) {
                    $query->where('category_id', '=', $value);
                });
            }
            );
        $this->crud->addFilter([
            'name'        => 'tag',
            'type'        => 'select2_ajax',
            'label'       => 'Tag',
            'placeholder' => 'Pick a tag'
        ],
            url('admin/posts/ajax-tag-options'), // the ajax route
               function ($value) { // if the filter is active
                   $this->crud->addClause('whereHas', 'tag', function ($query) use ($value) {
                       $query->where('tag_id', '=', $value);
                   });
               }
          );
        $this->crud->addFilter([
            'type'  => 'date_range',
            'name'  => 'created_at',
            'label' => 'Date range'
        ],
            false,
            function ($value) { // if the filter is active, apply these constraints
                 $dates = json_decode($value);
                 $this->crud->addClause('where', 'created_at', '>=', $dates->from);
                 $this->crud->addClause('where', 'created_at', '<=', $dates->to . ' 23:59:59');
            });
            $this->crud->addFilter([
                'name'  => 'status',
                'type'  => 'dropdown',
                'label' => 'Status'
            ], [
                0 => 'Private',
                1 => 'Published'
            ], function($value) { // if the filter is active
                 $this->crud->addClause('where', 'status', $value);
            });
        $this->crud->addFilter([
            'name'  => 'allow_comments',
            'type'  => 'dropdown',
            'label' => 'Allow Comments'
        ], [
            0 => 'No',
            1 => 'Yes'
        ], function($value) { // if the filter is active
            $this->crud->addClause('where', 'allow_comments', $value);
        });
        $this->crud->addColumns([
            [ // n-n relationship (with pivot table)
                'label'     => "Category", // Table column heading
                'type'      => 'relationship',
                'name'      => 'category', // the method that defines the relationship in your Model
                'entity'    => 'category', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => Category::class, // foreign key model
            ],
            [ // n-n relationship (with pivot table)
                'label'     => "Tags", // Table column heading
                'type'      => 'relationship',
                'name'      => 'tag', // the method that defines the relationship in your Model
                'entity'    => 'tag', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => Tag::class, // foreign key model
            ],
        ]);
        $this->crud->addColumn(
                [
                    'name'  => 'custom_fields',
                    'label' => 'Custom Fields',
                    'type'  => 'table',
                    'columns' =>
                        [
                        'name'        => 'name',
                        'content'        => 'content',
                        ],
                ]
        );
        $this->crud->addColumns([
            [
                'name'  => 'status',
                'label' => 'Status',
                'type'  => 'boolean',
                // optionally override the Yes/No texts
                'options' => [1 => 'Published', 0 => 'Private'],
                'wrapper' => [
                    'element' => 'span',
                    'class'   => function ($crud, $column, $entry, $related_key) {
                        if ($column['text'] == 'Published') {
                            return 'badge badge-success';
                        }

                        return 'badge badge-default';
                    },
                ],
            ],
            [
                'name'  => 'allow_comments',
                'label' => 'Allow Comments',
                'type'  => 'boolean',
                // optionally override the Yes/No texts
                'options' => [1 => 'Yes', 0 => 'No'],
                'wrapper' => [
                    'element' => 'span',
                    'class'   => function ($crud, $column, $entry, $related_key) {
                        if ($column['text'] == 'Yes') {
                            return 'badge badge-success';
                        }
                        return 'badge badge-default';
                    },
                ],
            ]
        ]);
        CRUD::addColumn([
                'name'     => 'created_at',
                'label'    => 'Created At',
                'type'     => 'closure',
                'function' => function($entry) {
                    return '<h3>Created on</h3> '.$entry->created_at;
                }
        ]);
        CRUD::addColumn([
            'name'  => 'updated_at', // The db column name
            'label' => 'Updated At', // Table column heading
            'type'  => 'datetime',
             'format' => 'Y-M-D H:m:s', // use something else than the base.default_datetime_format config value
        ]);
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
        $this->crud->enableDetailsRow();
        $this->crud->setDetailsRowView('vendor.backpack.crud.details_row.post');
        $this->crud->enableExportButtons();
        $this->crud->addButtonFromModelFunction('line', 'open_google', 'openGoogle', 'beginning');
    }
    public function setupShowOperation(){

        $this->crud->addButton('line', 'update', 'view', 'crud::buttons.edit',"beginning");
        $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete');

        $this->crud->addColumn(
            [
                'name'  => 'custom_fields',
                'label' => 'Custom Fields',
                'type'  => 'table',
                'columns' =>
                    [
                        'name'        => 'name',
                        'content'        => 'content',
                    ],
            ]
        );
        CRUD::column('title')->makeFirst();
        $this->crud->addColumn([
            'name'  => 'description', // The db column name
            'label' => 'Description', // Table column heading
            'type'  => 'markdown',
        ]);
        $this->crud->addColumn([
            'name'  => 'url',
            'label' => 'Send TrackBacks', // Table column heading
            'type'  => 'model_function',
            'function_name' => 'getSlugWithLink',
        ]);
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
        $this->crud->addColumns([
            [ // n-n relationship (with pivot table)
                'label'     => "Category", // Table column heading
                'type'      => 'relationship',
                'name'      => 'category', // the method that defines the relationship in your Model
                'entity'    => 'category', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => Category::class, // foreign key model
            ],
            [ // n-n relationship (with pivot table)
                'label'     => "Tags", // Table column heading
                'type'      => 'relationship',
                'name'      => 'tag', // the method that defines the relationship in your Model
                'entity'    => 'tag', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => Tag::class, // foreign key model
            ],
        ]);
        $this->crud->addColumn([
            'name' => 'image', // The db column name
            'label' => "Post Image", // Table column heading
            'type' => 'image',
            "disk" =>$this->crud->getCurrentEntry()->disk,
            "upload" =>true,
            'height' => '150px',
            'width'  => '130px'
        ]);
        CRUD::addColumn([
                'name'  => 'format_id',
                'label' => 'Format',
                'type'  => 'radio',
                // optionally override the Yes/No texts
                'options' => [
                    1 => "Standard",
                    2 => "Aside",
                    3 => "Image",
                    4 => "Video",
                    5 => "Audio",
                    6 => "Quote",
                    7 => "Link",
                    8 => "Gallery"
                ],
        ]);
        CRUD::addColumn('created_at');
        CRUD::addColumn('updated_at');

    }
    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setCreateContentClass('col-md-12 col-md-offset-2');
        CRUD::setValidation(PostRequest::class);
        $this->crud->addFields([
                [
                    'label'     => "Category",
                    'type'      => 'relationship',
                    'name'      => 'category', // the method that defines the relationship in your Model

                    // optional
                    'entity'    => 'category', // the method that defines the relationship in your Model
                    'model'     => Category::class, // foreign key model
                    'attribute' => 'name', // foreign key attribute that is shown to user
                    'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
                    "inline_create"=>true,
                    'ajax' => true,

                    // also optional
                    'options'   => (function ($query) {
                        return $query->orderBy('id', 'ASC')->get();
                    }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
                    'data_source'       => backpack_url('post/fetch/category'),
                    'wrapper' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'label'     => "Format",
                    'type'      => 'radio',
                    'name'      => 'format_id',
                    'default'   => 1,
                    // also optional
                    'options'     => [
                        // the key will be stored in the db, the value will be shown as label;
                        1 => "Standard",
                        2 => "Aside",
                        3 => "Image",
                        4 => "Video",
                        5 => "Audio",
                        6 => "Quote",
                        7 => "Link",
                        8 => "Gallery"
                    ],
                    'wrapper' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'label'     => "Tags",
                    'type'      => 'relationship',
                    'name'      => 'tag', // the method that defines the relationship in your Model

                    // optional
                    'entity'    => 'tag', // the method that defines the relationship in your Model
                    'model'     => Tag::class, // foreign key model
                    'attribute' => 'name', // foreign key attribute that is shown to user
                    'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
                    "inline_create"=>true,
                    'ajax' => true,

                    // also optional
                    'options'   => (function ($query) {
                        return $query->orderBy('id', 'ASC')->get();
                    }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
                    'wrapper' => ['class' => 'form-group col-md-4'],
                    'data_source'       => backpack_url('post/fetch/tag'),
                ],
        ]);
        $this->crud->addField([
            "label"=>"Disk",
            "name"=>"disk",
            "type"=>"hidden",
            "value"=>config("save_disk.post_thumb"),
        ]);
        CRUD::addField([
            "name"=>"title",
            'type'  => 'text',
            "label"=>"title"
        ]);
        $abc = CRUD::addField(
            [   // repeatable
                'name'  => 'custom_fields',
                'label' => 'Testimonials',
                'type'  => 'repeatable',
                'fields' => [
                    [
                        'name'    => 'name',
                        'type'    => 'text',
                        'label'   => 'Name',
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ],
                    [
                        'name'    => 'content',
                        'type'    => 'text',
                        'label'   => 'Content',
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ]
                ],
            ]
        );
        CRUD::addField([
            "name"=>"excerpt",
            'type'  => 'text',
            "label"=>"Excerpt"
        ]);
        CRUD::addField([
            "name"=>"url",
            'type'  => 'text',
            "label"=>"Send TrackBacks"
        ]);
        CRUD::addField([
            "name"=>"description",
            'type'  => 'ckeditor',
            "label"=>"Description",
        ]);
        $this->crud->addField([
            'label'        => "Post Image",
            'name'         => "image",
            'filename'     => "image_filename", // set to null if not needed
            'type'         => 'upload',
            "disk"         =>config("save_disk.post_thumb"),
            "upload"       =>true,
            'aspect_ratio' => 1, // set to 0 to allow any aspect ratio
            'crop'         => true, // set to true to allow cropping, false to disable
            'src'          => NULL, // null to read straight from DB, otherwise set to model accessor function
        ]);
        CRUD::addField([
            'label'     => 'Published',
            'name'  => 'status',
            'type'  => 'checkbox',
            "default"=>"1",
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);
        CRUD::addField([
            'label'     => 'Allow Comments',
            'name'  => 'allow_comments',
            'type'  => 'checkbox',
            "default"=>"1",
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            "label"=>"User",
            "name"=>"user_id",
            "type"=>"hidden",
            "default"=>backpack_user()->id
        ]);
        CRUD::setValidation(PostRequest::class);
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */

    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->crud->setEditContentClass("col-md-12 col-md-offset-2");
        $post = Post::where("id",$this->crud->getCurrentEntryId())->first();
        if(!backpack_user()->can("update",$post)){
            abort(403);
        }
        $this->setupCreateOperation();
        $this->crud->addSaveActions([
            [
                'name' => 'Edit and Back',
                'visible' => function($crud) {
                    return true;
                },
                'redirect' => function($crud, $request, $itemId) {
                    return $crud->route;
                },
            ],
            [
                'name' => 'Edit and Back',
                'visible' => function($crud) {
                    return true;
                },
                'redirect' => function($crud, $request, $itemId) {
                    \Alert::add('success', '<strong>Got it</strong><br>This is HTML in a green bubble.');
                    return $crud->route;
                },
            ],
        ]);
        $this->crud->replaceSaveActions(
            [
                'name' => 'Edit and Back',
                'visible' => function($crud) {
                    return true;
                },
                'redirect' => function($crud, $request, $itemId) {

                    return $crud->route;
                },
            ]
        );
    }
    public function fetchCategory()
    {
        return $this->fetch(\App\Models\Category::class);
    }
    public function fetchTag()
    {
        return $this->fetch(\App\Models\Tag::class);
    }
    public function tagOptions(Request $request) {
        $term = $request->input('term');
        $options = Tag::where('name', 'like', '%'.$term.'%')->get()->pluck('name', 'id');
        return $options;
    }
    public function categoryOptions(Request $request){
        $term = $request->input('term');
        $options = Category::where('name', 'like', '%'.$term.'%')->get()->pluck('name', 'id');
        return $options;
    }
}
