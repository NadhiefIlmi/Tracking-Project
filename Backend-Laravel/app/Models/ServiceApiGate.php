<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceApiGate extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'service_api_gates';
    protected $fillable = [
        'id',
        'id_deployment',
        'api_gate',
        'service_name',
        'protocol',
        'path',
    ];

    public function deployment() { return $this->belongsTo(Deployment::class, 'id_deployment'); }
    public function routeApiGates() { return $this->hasMany(RouteApiGate::class, 'id_service_api_gate'); }
} 