<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteApiGate extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'route_api_gates';
    protected $fillable = [
        'id',
        'id_app',
        'id_service_api_gate',
        'route_name',
        'path',
        'protocol',
    ];

    public function app() { return $this->belongsTo(App::class, 'id_app'); }
    public function serviceApiGate() { return $this->belongsTo(ServiceApiGate::class, 'id_service_api_gate'); }
} 