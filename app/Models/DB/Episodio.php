<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episodio extends Model
{
    use HasFactory;

    public function temporada()
    {
        return $this->belongsTo(Temporada::class);
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }
}
