<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensualidad extends Model
{
    use HasFactory;

    protected $table = 'Mensualidades';

    protected $fillable = ['id_cotizacion', 'monto_mensualidad', 'pagada'];
}
