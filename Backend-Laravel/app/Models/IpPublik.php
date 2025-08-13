<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpPublik extends Model
{
    use HasFactory;
    protected $table = 'ip_publiks';
    protected $fillable = [
        'id_waf',
        'ip_publik',
    ];

    public function waf() { return $this->belongsTo(Waf::class, 'id_waf'); }
    public function inpervas() { return $this->hasMany(Inperva::class, 'id_ip_publik'); }
} 