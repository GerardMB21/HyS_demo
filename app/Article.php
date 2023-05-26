<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function family() {
        return $this->belongsTo(Classification::class);
    }
}
