<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    use HasFactory,CrudTrait;
    public $timestamps = true;
    protected $table="formats";
    protected $primaryKey="id";
    protected $fillable=[
      "name"
    ];

}
