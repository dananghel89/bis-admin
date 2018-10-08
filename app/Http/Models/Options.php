<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Options extends Model
{
    /**
     * Disable timestamps for table.
     *
     * @return void
     */
	public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'value'
    ];
}
