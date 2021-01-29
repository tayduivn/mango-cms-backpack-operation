<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Permission\Models\Role as OriginalRole;

class Role extends OriginalRole
{
    use CrudTrait;
    protected $table="roles";
    protected $fillable = ['name', 'guard_name', 'updated_at', 'created_at'];
    protected $primaryKey="id";
}
