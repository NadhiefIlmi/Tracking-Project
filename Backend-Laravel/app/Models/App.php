<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasFactory;
    protected $table = 'apps';
    protected $fillable = [
        'proses_bisnis',
        'nama_singkat_proses_bisnis',
        'unit_kerja_pemilik_proses_bisnis',
        'jenis_layanan',
        'nama_aplikasi',
        'penanggung_jawab_aplikasi',
    ];

    public function projects() { return $this->hasMany(Project::class, 'id_app'); }
    public function routeApiGates() { return $this->hasMany(RouteApiGate::class, 'id_app'); }
} 