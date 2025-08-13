<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waf extends Model
{
    use HasFactory;
    protected $table = 'wafs';
    protected $fillable = [
        'id_deployment',
        'ip_waf',
    ];

    public function deployment() { return $this->belongsTo(Deployment::class, 'id_deployment'); }
    public function ippubliks() { return $this->hasMany(IpPublik::class, 'id_waf'); }
} 