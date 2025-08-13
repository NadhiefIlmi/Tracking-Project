<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $table = 'projects';
    protected $fillable = [
        'id_app',
        'project_name',
        'base_project',
        'fungsi',
        'technology',
        'framework',
        'version',
    ];

    public function app() { return $this->belongsTo(App::class, 'id_app'); }
    public function deployments() { return $this->hasMany(Deployment::class, 'id_project'); }
} 