<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempBarang extends Model
{
    use HasFactory;
    protected $table = 'barang_temp';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'detil_id',
        'descofgoods',
    ];
}