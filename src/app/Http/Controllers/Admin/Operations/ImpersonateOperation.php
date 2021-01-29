<?php

namespace CompanyName\ModerateOperation\app\Http\Controllers\Admin\Operations;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Prologue\Alerts\Facades\Alert;

trait ImpersonateOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupImpersonateRoutes($segment, $routeName, $controller)
    {
            Route::get($segment.'/{id}/impersonate', [
                'as'        => $routeName.'.impersonate',
                'uses'      => $controller.'@impersonate',
                'operation' => 'impersonate',
            ]);
        Route::get('stop-impersonating', function() {
            backpack_user()->stopImpersonating();
            \Alert::success('Impersonating stopped.')->flash();
            return redirect()->back();
        });
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupImpersonateDefaults()
    {
        if(backpack_user()->hasRole("Admin")){
            $this->crud->allowAccess('impersonate');
        }
        $this->crud->operation('impersonate', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->addButton('line', 'impersonate', 'view', 'crud::buttons.impersonate');
        });
    }

    /**
     * Impersonate that user and redirect to his profile.
     *
     * @return Response
     */
    public function impersonate()
    {
        $this->crud->hasAccessOrFail('impersonate');

        $entry = $this->crud->getCurrentEntry();

        backpack_user()->setImpersonating($entry->id);

        Alert::success('Impersonating '.$entry->name.' (id '.$entry->id.').')->flash();

        // load the view
        return redirect('/admin/user');
    }
}
