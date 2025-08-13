<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DependencyPackage extends Model
{
    use HasFactory;
    protected $table = 'dependency_packages';
    protected $fillable = [
        'id_deployment',
        'dependency_name',
        'version',
    ];

    public function deployment() { return $this->belongsTo(Deployment::class, 'id_deployment'); }
} 