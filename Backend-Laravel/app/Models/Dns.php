<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dns extends Model
{
    use HasFactory;
    protected $table = 'dns';
    protected $fillable = [
        'id_inperva',
        'name',
        'type',
    ];

    public function inperva() { return $this->belongsTo(Inperva::class, 'id_inperva'); }
} 