<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class Temporada extends Model
{
    protected $table = "temporada";

    protected $guarded = false;

    public function episodios()
    {
        return $this->hasMany(Episodio::class);
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }
}
