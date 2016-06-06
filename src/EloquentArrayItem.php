<?php

namespace Sukohi\EloquentArray;

use Illuminate\Database\Eloquent\Model;

class EloquentArrayItem extends Model
{
    public $guarded = ['id'];
	public $timestamps = false;
}
