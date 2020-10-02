<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
    protected $table = 'mentors';

    protected $fillable = [
        'name','profile','email','prosession'
    ];

    public function course()
    {
        return $this->hasMany('App\Course')->orderBy('id','ASC');
    }
}
