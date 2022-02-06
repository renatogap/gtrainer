<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $table = "serie";

    protected $guarded = false;

    public function temporadas()
    {
        return $this->hasMany(Temporada::class, 'fk_serie');
    }
}
