<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    use HasFactory;
    protected $table = 'deployments';
    protected $fillable = [
        'id_project',
        'namespace',
        'service_name',
        'type',
        'cluster_ip',
        'external_ip',
        'port',
        'jenis_app',
        'tahap',
        'env',
        'nginx',
        'php',
    ];

    public function project() { return $this->belongsTo(Project::class, 'id_project'); }
    public function serviceApiGates() { return $this->hasMany(ServiceApiGate::class, 'id_deployment'); }
    public function dependencyPackages() { return $this->hasMany(DependencyPackage::class, 'id_deployment'); }
    public function wafs() { return $this->hasMany(Waf::class, 'id_deployment'); }
} 