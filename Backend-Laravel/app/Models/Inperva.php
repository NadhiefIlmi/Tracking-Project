<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inperva extends Model
{
    use HasFactory;
    protected $table = 'inpervas';
    protected $fillable = [
        'id_ip_publik',
        'name',
    ];

    public function ipPublik() { return $this->belongsTo(IpPublik::class, 'id_ip_publik'); }
    public function dns() { return $this->hasMany(Dns::class, 'id_inperva'); }
} 