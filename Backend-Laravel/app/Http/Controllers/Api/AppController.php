<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\App as AppModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppController extends Controller
{

    // Mendapatkan detail relasi aplikasi
    public function detail($id)
    {
        try {
            $app = AppModel::with([
                'projects.deployments.dependencyPackages',
                'projects.deployments.serviceApiGates.routeApiGates',
                'projects.deployments.wafs.ippubliks.inpervas.dns',
                'routeApiGates',
            ])->findOrFail($id);
    
            // Debug: Cek apakah relasi WAF-IP Publik berfungsi
            \Log::info('=== DEBUG RELASI WAF-IP PUBLIK ===');
            foreach ($app->projects as $project) {
                foreach ($project->deployments as $deployment) {
                    if ($deployment->wafs->count() > 0) {
                        \Log::info("Deployment {$deployment->id} memiliki {$deployment->wafs->count()} WAF");
                        foreach ($deployment->wafs as $waf) {
                            \Log::info("WAF {$waf->id} memiliki {$waf->ippubliks->count()} IP Publik");
                            if ($waf->ippubliks->count() > 0) {
                                foreach ($waf->ippubliks as $ipPublik) {
                                    \Log::info("IP Publik {$ipPublik->id}: {$ipPublik->ip_publik}");
                                }
                            }
                        }
                    }
                }
            }
            \Log::info('=== END DEBUG ===');
    
            // Test: Cek data WAF dan IP Publik yang ada
            \Log::info('=== TEST DATA WAF DAN IP PUBLIK ===');
            $wafs = \App\Models\Waf::with('ippubliks')->get();
            \Log::info("Total WAF: {$wafs->count()}");
            foreach ($wafs as $waf) {
                \Log::info("WAF {$waf->id} (Deployment: {$waf->id_deployment}, IP: {$waf->ip_waf}) memiliki {$waf->ippubliks->count()} IP Publik");
            }
            
            $ipPubliks = \App\Models\IpPublik::all();
            \Log::info("Total IP Publik: {$ipPubliks->count()}");
            foreach ($ipPubliks as $ipPublik) {
                \Log::info("IP Publik {$ipPublik->id} (WAF: {$ipPublik->id_waf}, IP: {$ipPublik->ip_publik})");
            }
            \Log::info('=== END TEST ===');
    
            return response()->json($app);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Mengembalikan semua aplikasi
    public function index()
    {
        return response()->json(AppModel::with([
            'projects.deployments.dependencyPackages',
            'projects.deployments.serviceApiGates.routeApiGates',
            'projects.deployments.wafs.ippubliks.inpervas.dns',
            'routeApiGates',
        ])->get());
    }

    // Menyimpan aplikasi baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'proses_bisnis' => 'required|string|max:255',
            'nama_singkat_proses_bisnis' => 'required|string|max:255',
            'unit_kerja_pemilik_proses_bisnis' => 'required|string|max:255',
            'jenis_layanan' => 'required|string|max:255',
            'nama_aplikasi' => 'required|string|max:255',
            'penanggung_jawab_aplikasi' => 'required|string|max:255',
            'projects' => 'array',
            'projects.*.project_name' => 'nullable|string',
            'projects.*.base_project' => 'nullable|string',
            'projects.*.fungsi' => 'nullable|string',
            'projects.*.technology' => 'nullable|string',
            'projects.*.framework' => 'nullable|string',
            'projects.*.version' => 'nullable|string',
            'projects.*.deployments' => 'array',
            'projects.*.deployments.*.namespace' => 'nullable|string',
            'projects.*.deployments.*.service_name' => 'nullable|string',
            'projects.*.deployments.*.type' => 'nullable|string',
            'projects.*.deployments.*.cluster_ip' => 'nullable|string',
            'projects.*.deployments.*.external_ip' => 'nullable|string',
            'projects.*.deployments.*.port' => 'nullable|string',
            'projects.*.deployments.*.jenis_app' => 'nullable|string',
            'projects.*.deployments.*.tahap' => 'nullable|string',
            'projects.*.deployments.*.env' => 'nullable|string',
            'projects.*.deployments.*.service_api_gates' => 'array',
            'projects.*.deployments.*.service_api_gates.*.api_gate' => 'nullable|string',
            'projects.*.deployments.*.service_api_gates.*.service_name' => 'nullable|string',
            'projects.*.deployments.*.service_api_gates.*.protocol' => 'nullable|string',
            'projects.*.deployments.*.service_api_gates.*.path' => 'nullable|string',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates' => 'array',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates.*.route_name' => 'nullable|string',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates.*.path' => 'nullable|string',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates.*.protocol' => 'nullable|string',
            'projects.*.deployments.*.dependency_packages' => 'array',
            'projects.*.deployments.*.dependency_packages.*.dependency_name' => 'nullable|string',
            'projects.*.deployments.*.dependency_packages.*.version' => 'nullable|string',
            'projects.*.deployments.*.wafs' => 'array',
            'projects.*.deployments.*.wafs.*.ip_waf' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks' => 'array',
            'projects.*.deployments.*.wafs.*.ippubliks.*.ip_publik' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas' => 'array',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.name' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.dns' => 'array',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.dns.*.name' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.dns.*.type' => 'nullable|string',
        ]);
        
        \DB::beginTransaction();
        try {
            $app = AppModel::create($validated);
            if (!empty($request->projects)) {
                foreach ($request->projects as $projectData) {
                    try {
                        $project = $app->projects()->create([
                            'project_name' => $projectData['project_name'] ?? '',
                            'base_project' => $projectData['base_project'] ?? '',
                            'fungsi' => $projectData['fungsi'] ?? '',
                            'technology' => $projectData['technology'] ?? '',
                            'framework' => $projectData['framework'] ?? '',
                            'version' => $projectData['version'] ?? '',
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Gagal simpan project: ' . $e->getMessage(), ['project' => $projectData]);
                        throw $e;
                    }
                    if (!empty($projectData['deployments'])) {
                        foreach ($projectData['deployments'] as $deploymentData) {
                            try {
                                $deployment = $project->deployments()->create([
                                    'namespace' => $deploymentData['namespace'] ?? '',
                                    'service_name' => $deploymentData['service_name'] ?? '',
                                    'type' => $deploymentData['type'] ?? '',
                                    'cluster_ip' => $deploymentData['cluster_ip'] ?? '',
                                    'external_ip' => $deploymentData['external_ip'] ?? '',
                                    'port' => $deploymentData['port'] ?? '',
                                    'jenis_app' => $deploymentData['jenis_app'] ?? '',
                                    'tahap' => $deploymentData['tahap'] ?? '',
                                    'env' => $deploymentData['env'] ?? '',
                                ]);
                            } catch (\Exception $e) {
                                \Log::error('Gagal simpan deployment: ' . $e->getMessage(), ['deployment' => $deploymentData]);
                                throw $e;
                            }
                            if (!empty($deploymentData['service_api_gates'])) {
                                foreach ($deploymentData['service_api_gates'] as $serviceApiGateData) {
                                    try {
                                        $serviceApiGate = $deployment->serviceApiGates()->create([
                                            'id' => Str::uuid()->toString(),
                                            'api_gate' => $serviceApiGateData['api_gate'] ?? '',
                                            'service_name' => $serviceApiGateData['service_name'] ?? '',
                                            'protocol' => $serviceApiGateData['protocol'] ?? '',
                                            'path' => $serviceApiGateData['path'] ?? '',
                                        ]);
                                    } catch (\Exception $e) {
                                        \Log::error('Gagal simpan service_api_gate: ' . $e->getMessage(), ['service_api_gate' => $serviceApiGateData]);
                                        throw $e;
                                    }
                                    if (!empty($serviceApiGateData['route_api_gates']) && is_array($serviceApiGateData['route_api_gates'])) {
                                        foreach ($serviceApiGateData['route_api_gates'] as $routeApiGateData) {
                                            // Skip jika data tidak valid atau ada masalah deep nesting
                                            if (!is_array($routeApiGateData) || empty($routeApiGateData['route_name']) || 
                                                is_string($routeApiGateData) || !isset($routeApiGateData['route_name'])) {
                                                continue;
                                            }
                                            
                                            try {
                                                $serviceApiGate->routeApiGates()->create([
                                                    'id' => Str::uuid()->toString(),
                                                    'id_app' => $app->id,
                                                    'id_service_api_gate' => $serviceApiGate->id,
                                                    'route_name' => $routeApiGateData['route_name'] ?? '',
                                                    'path' => $routeApiGateData['path'] ?? '',
                                                    'protocol' => $routeApiGateData['protocol'] ?? '',
                                                ]);
                                            } catch (\Exception $e) {
                                                \Log::error('Gagal simpan route_api_gate: ' . $e->getMessage(), [
                                                    'route_api_gate' => $routeApiGateData,
                                                    'id_app' => $app->id,
                                                    'id_service_api_gate' => $serviceApiGate->id
                                                ]);
                                                throw $e;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Import WAF, IP Publik, Inperva, DNS
                            if (!empty($deploymentData['wafs']) && is_array($deploymentData['wafs'])) {
                                foreach ($deploymentData['wafs'] as $wafData) {
                                    // Skip jika data tidak valid
                                    if (!is_array($wafData) || empty($wafData['ip_waf'])) {
                                        continue;
                                    }
                                    
                                    try {
                                        $waf = $deployment->wafs()->create([
                                            'ip_waf' => $wafData['ip_waf'] ?? '',
                                            'id_deployment' => $deployment->id,
                                        ]);
                                        
                                        // Import IP Publik untuk WAF ini
                                        if (!empty($wafData['ippubliks']) && is_array($wafData['ippubliks'])) {
                                            foreach ($wafData['ippubliks'] as $ipPublikData) {
                                                if (!is_array($ipPublikData) || empty($ipPublikData['ip_publik'])) {
                                                    continue;
                                                }
                                                
                                                try {
                                                    $ipPublik = $waf->ippubliks()->create([
                                                        'ip_publik' => $ipPublikData['ip_publik'] ?? '',
                                                        'id_waf' => $waf->id,
                                                    ]);
                                                    
                                                    // Import Inperva untuk IP Publik ini
                                                    if (!empty($ipPublikData['inpervas']) && is_array($ipPublikData['inpervas'])) {
                                                        foreach ($ipPublikData['inpervas'] as $inpervaData) {
                                                            if (!is_array($inpervaData) || empty($inpervaData['name'])) {
                                                                continue;
                                                            }
                                                            
                                                            try {
                                                                $inperva = $ipPublik->inpervas()->create([
                                                                    'name' => $inpervaData['name'] ?? '',
                                                                    'id_ippublik' => $ipPublik->id,
                                                                ]);
                                                                
                                                                // Import DNS untuk Inperva ini
                                                                if (!empty($inpervaData['dns']) && is_array($inpervaData['dns'])) {
                                                                    foreach ($inpervaData['dns'] as $dnsData) {
                                                                        if (!is_array($dnsData) || empty($dnsData['name'])) {
                                                                            continue;
                                                                        }
                                                                        
                                                                        try {
                                                                            $inperva->dns()->create([
                                                                                'name' => $dnsData['name'] ?? '',
                                                                                'type' => $dnsData['type'] ?? '',
                                                                                'id_inperva' => $inperva->id,
                                                                            ]);
                                                                        } catch (\Exception $e) {
                                                                            \Log::error('Gagal simpan DNS: ' . $e->getMessage(), ['dns' => $dnsData]);
                                                                            throw $e;
                                                                        }
                                                                    }
                                                                }
                                                            } catch (\Exception $e) {
                                                                \Log::error('Gagal simpan Inperva: ' . $e->getMessage(), ['inperva' => $inpervaData]);
                                                                throw $e;
                                                            }
                                                        }
                                                    }
                                                } catch (\Exception $e) {
                                                    \Log::error('Gagal simpan IP Publik: ' . $e->getMessage(), ['ip_publik' => $ipPublikData]);
                                                    throw $e;
                                                }
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        \Log::error('Gagal simpan WAF: ' . $e->getMessage(), ['waf' => $wafData]);
                                        throw $e;
                                    }
                                }
                            }
                            
                            // Import dependency packages
                            if (!empty($deploymentData['dependency_packages']) && is_array($deploymentData['dependency_packages'])) {
                                foreach ($deploymentData['dependency_packages'] as $dependencyData) {
                                    // Skip jika data tidak valid
                                    if (!is_array($dependencyData) || empty($dependencyData['dependency_name'])) {
                                        continue;
                                    }
                                    
                                    try {
                                        $deployment->dependencyPackages()->create([
                                            'dependency_name' => $dependencyData['dependency_name'] ?? '',
                                            'version' => $dependencyData['version'] ?? '',
                                        ]);
                                    } catch (\Exception $e) {
                                        \Log::error('Gagal simpan dependency package: ' . $e->getMessage(), ['dependency' => $dependencyData]);
                                        throw $e;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            \DB::commit();
            $app->load('projects.deployments.dependencyPackages', 'projects.deployments.serviceApiGates.routeApiGates', 'projects.deployments.wafs.ippubliks.inpervas.dns');
            return response()->json($app, 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Gagal simpan aplikasi/relasi: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Mengupdate aplikasi
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'proses_bisnis' => 'sometimes|required|string|max:255',
            'nama_singkat_proses_bisnis' => 'sometimes|required|string|max:255',
            'unit_kerja_pemilik_proses_bisnis' => 'sometimes|required|string|max:255',
            'jenis_layanan' => 'sometimes|required|string|max:255',
            'nama_aplikasi' => 'sometimes|required|string|max:255',
            'penanggung_jawab_aplikasi' => 'sometimes|required|string|max:255',
            'projects' => 'array',
            'projects.*.id' => 'sometimes|integer|exists:projects,id',
            'projects.*.project_name' => 'required_with:projects|string',
            'projects.*.base_project' => 'required_with:projects|string',
            'projects.*.fungsi' => 'required_with:projects|string',
            'projects.*.technology' => 'required_with:projects|string',
            'projects.*.framework' => 'required_with:projects|string',
            'projects.*.version' => 'required_with:projects|string',
            'projects.*.deployments' => 'array',
            'projects.*.deployments.*.id' => 'sometimes|integer|exists:deployments,id',
            'projects.*.deployments.*.namespace' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.service_name' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.type' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.cluster_ip' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.external_ip' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.port' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.jenis_app' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.tahap' => 'required_with:projects.*.deployments|string',
            'projects.*.deployments.*.env' => 'required_with:projects.*.deployments|string',
          
            'projects.*.deployments.*.service_api_gates' => 'array',
            'projects.*.deployments.*.service_api_gates.*.id' => 'sometimes|string|exists:service_api_gates,id',
            'projects.*.deployments.*.service_api_gates.*.api_gate' => 'required_with:projects.*.deployments.*.service_api_gates|string',
            'projects.*.deployments.*.service_api_gates.*.service_name' => 'required_with:projects.*.deployments.*.service_api_gates|string',
            'projects.*.deployments.*.service_api_gates.*.protocol' => 'required_with:projects.*.deployments.*.service_api_gates|string',
            'projects.*.deployments.*.service_api_gates.*.path' => 'required_with:projects.*.deployments.*.service_api_gates|string',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates' => 'array',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates.*.id' => 'sometimes|string|exists:route_api_gates,id',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates.*.route_name' => 'required_with:projects.*.deployments.*.service_api_gates.*.route_api_gates|string',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates.*.path' => 'required_with:projects.*.deployments.*.service_api_gates.*.route_api_gates|string',
            'projects.*.deployments.*.service_api_gates.*.route_api_gates.*.protocol' => 'required_with:projects.*.deployments.*.service_api_gates.*.route_api_gates|string',
            'projects.*.deployments.*.dependency_packages' => 'array',
            'projects.*.deployments.*.dependency_packages.*.id' => 'sometimes|integer|exists:dependency_packages,id',
            'projects.*.deployments.*.dependency_packages.*.dependency_name' => 'required_with:projects.*.deployments.*.dependency_packages|string',
            'projects.*.deployments.*.dependency_packages.*.version' => 'nullable|string',
            'projects.*.deployments.*.wafs' => 'array',
            'projects.*.deployments.*.wafs.*.id' => 'sometimes|integer|exists:wafs,id',
            'projects.*.deployments.*.wafs.*.ip_waf' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks' => 'array',
            'projects.*.deployments.*.wafs.*.ippubliks.*.id' => 'sometimes|integer|exists:ip_publiks,id',
            'projects.*.deployments.*.wafs.*.ippubliks.*.ip_publik' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas' => 'array',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.id' => 'sometimes|integer|exists:inpervas,id',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.name' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.dns' => 'array',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.dns.*.id' => 'sometimes|integer|exists:dns,id',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.dns.*.name' => 'nullable|string',
            'projects.*.deployments.*.wafs.*.ippubliks.*.inpervas.*.dns.*.type' => 'nullable|string',
        ]);

        \DB::beginTransaction();
        try {
            $app = AppModel::findOrFail($id);
            $app->update($validated);

            // Update relasi projects
            if (!empty($request->projects)) {
                $projectIds = [];
                foreach ($request->projects as $projectData) {
                    if (!empty($projectData['id'])) {
                        // Update project lama
                        $project = $app->projects()->find($projectData['id']);
                        if ($project) {
                            $project->update([
                                'project_name' => $projectData['project_name'] ?? '',
                                'base_project' => $projectData['base_project'] ?? '',
                                'fungsi' => $projectData['fungsi'] ?? '',
                                'technology' => $projectData['technology'] ?? '',
                                'framework' => $projectData['framework'] ?? '',
                                'version' => $projectData['version'] ?? '',
                            ]);
                        }
                    } else {
                        // Tambah project baru
                        $project = $app->projects()->create([
                            'project_name' => $projectData['project_name'] ?? '',
                            'base_project' => $projectData['base_project'] ?? '',
                            'fungsi' => $projectData['fungsi'] ?? '',
                            'technology' => $projectData['technology'] ?? '',
                            'framework' => $projectData['framework'] ?? '',
                            'version' => $projectData['version'] ?? '',
                        ]);
                    }
                    $projectIds[] = $project->id;



                    // Update deployments
                    if (!empty($projectData['deployments'])) {
                        $deploymentIds = [];
                        foreach ($projectData['deployments'] as $deploymentData) {
                            if (!empty($deploymentData['id'])) {
                                $deployment = $project->deployments()->find($deploymentData['id']);
                                if ($deployment) {
                                    $deployment->update([
                                        'namespace' => $deploymentData['namespace'] ?? '',
                                        'service_name' => $deploymentData['service_name'] ?? '',
                                        'type' => $deploymentData['type'] ?? '',
                                        'cluster_ip' => $deploymentData['cluster_ip'] ?? '',
                                        'external_ip' => $deploymentData['external_ip'] ?? '',
                                        'port' => $deploymentData['port'] ?? '',
                                        'jenis_app' => $deploymentData['jenis_app'] ?? '',
                                        'tahap' => $deploymentData['tahap'] ?? '',
                                        'env' => $deploymentData['env'] ?? '',
                                    ]);
                                }
                            } else {
                                $deployment = $project->deployments()->create([
                                    'namespace' => $deploymentData['namespace'] ?? '',
                                    'service_name' => $deploymentData['service_name'] ?? '',
                                    'type' => $deploymentData['type'] ?? '',
                                    'cluster_ip' => $deploymentData['cluster_ip'] ?? '',
                                    'external_ip' => $deploymentData['external_ip'] ?? '',
                                    'port' => $deploymentData['port'] ?? '',
                                    'jenis_app' => $deploymentData['jenis_app'] ?? '',
                                    'tahap' => $deploymentData['tahap'] ?? '',
                                    'env' => $deploymentData['env'] ?? '',
                                ]);
                            }
                            $deploymentIds[] = $deployment->id;

                            // Update dependency packages
                            if (!empty($deploymentData['dependency_packages']) && is_array($deploymentData['dependency_packages'])) {
                                $dependencyPackageIds = [];
                                foreach ($deploymentData['dependency_packages'] as $dependencyData) {
                                    // Skip jika data tidak valid
                                    if (!is_array($dependencyData) || empty($dependencyData['dependency_name'])) {
                                        continue;
                                    }
                                    
                                    if (!empty($dependencyData['id'])) {
                                        $dependencyPackage = $deployment->dependencyPackages()->find($dependencyData['id']);
                                        if ($dependencyPackage) {
                                            $dependencyPackage->update([
                                                'dependency_name' => $dependencyData['dependency_name'] ?? '',
                                                'version' => $dependencyData['version'] ?? '',
                                            ]);
                                            $dependencyPackageIds[] = $dependencyPackage->id;
                                        }
                                    } else {
                                        $dependencyPackage = $deployment->dependencyPackages()->create([
                                            'dependency_name' => $dependencyData['dependency_name'] ?? '',
                                            'version' => $dependencyData['version'] ?? '',
                                        ]);
                                        $dependencyPackageIds[] = $dependencyPackage->id;
                                    }
                                }
                                // Hapus dependency packages yang tidak ada di request
                                $deployment->dependencyPackages()->whereNotIn('id', $dependencyPackageIds)->delete();
                            } else {
                                // Jika kosong, hapus semua dependency packages
                                $deployment->dependencyPackages()->delete();
                            }

                            // Update WAF, IP Publik, Inperva, DNS
                            if (!empty($deploymentData['wafs'])) {
                                $wafIds = [];
                                foreach ($deploymentData['wafs'] as $wafData) {
                                    if (!empty($wafData['id'])) {
                                        // Update WAF lama
                                        $waf = $deployment->wafs()->find($wafData['id']);
                                        if ($waf) {
                                            $waf->update([
                                                'ip_waf' => $wafData['ip_waf'] ?? '',
                                            ]);
                                        }
                                    } else {
                                        // Tambah WAF baru
                                        $waf = $deployment->wafs()->create([
                                            'ip_waf' => $wafData['ip_waf'] ?? '',
                                            'id_deployment' => $deployment->id,
                                        ]);
                                    }
                                    $wafIds[] = $waf->id;

                                    // Update IP Publik untuk WAF ini
                                    if (!empty($wafData['ippubliks'])) {
                                        $ipPublikIds = [];
                                        foreach ($wafData['ippubliks'] as $ipPublikData) {
                                            if (!empty($ipPublikData['id'])) {
                                                $ipPublik = $waf->ippubliks()->find($ipPublikData['id']);
                                                if ($ipPublik) {
                                                    $ipPublik->update([
                                                        'ip_publik' => $ipPublikData['ip_publik'] ?? '',
                                                    ]);
                                                }
                                            } else {
                                                $ipPublik = $waf->ippubliks()->create([
                                                    'ip_publik' => $ipPublikData['ip_publik'] ?? '',
                                                    'id_waf' => $waf->id,
                                                ]);
                                            }
                                            $ipPublikIds[] = $ipPublik->id;

                                            // Update Inperva untuk IP Publik ini
                                            if (!empty($ipPublikData['inpervas'])) {
                                                $inpervaIds = [];
                                                foreach ($ipPublikData['inpervas'] as $inpervaData) {
                                                    if (!empty($inpervaData['id'])) {
                                                        $inperva = $ipPublik->inpervas()->find($inpervaData['id']);
                                                        if ($inperva) {
                                                            $inperva->update([
                                                                'name' => $inpervaData['name'] ?? '',
                                                            ]);
                                                        }
                                                    } else {
                                                        $inperva = $ipPublik->inpervas()->create([
                                                            'name' => $inpervaData['name'] ?? '',
                                                            'id_ippublik' => $ipPublik->id,
                                                        ]);
                                                    }
                                                    $inpervaIds[] = $inperva->id;

                                                    // Update DNS untuk Inperva ini
                                                    if (!empty($inpervaData['dns'])) {
                                                        $dnsIds = [];
                                                        foreach ($inpervaData['dns'] as $dnsData) {
                                                            if (!empty($dnsData['id'])) {
                                                                $dns = $inperva->dns()->find($dnsData['id']);
                                                                if ($dns) {
                                                                    $dns->update([
                                                                        'name' => $dnsData['name'] ?? '',
                                                                        'type' => $dnsData['type'] ?? '',
                                                                    ]);
                                                                }
                                                            } else {
                                                                $dns = $inperva->dns()->create([
                                                                    'name' => $dnsData['name'] ?? '',
                                                                    'type' => $dnsData['type'] ?? '',
                                                                    'id_inperva' => $inperva->id,
                                                                ]);
                                                            }
                                                            $dnsIds[] = $dns->id;
                                                        }
                                                        // Hapus DNS yang tidak ada di request
                                                        $inperva->dns()->whereNotIn('id', $dnsIds)->delete();
                                                    } else {
                                                        // Jika kosong, hapus semua DNS
                                                        $inperva->dns()->delete();
                                                    }
                                                }
                                                // Hapus Inperva yang tidak ada di request
                                                $ipPublik->inpervas()->whereNotIn('id', $inpervaIds)->delete();
                                            } else {
                                                // Jika kosong, hapus semua Inperva
                                                $ipPublik->inpervas()->delete();
                                            }
                                        }
                                        // Hapus IP Publik yang tidak ada di request
                                        $waf->ippubliks()->whereNotIn('id', $ipPublikIds)->delete();
                                    } else {
                                        // Jika kosong, hapus semua IP Publik
                                        $waf->ippubliks()->delete();
                                    }
                                }
                                // Hapus WAF yang tidak ada di request
                                $deployment->wafs()->whereNotIn('id', $wafIds)->delete();
                            } else {
                                // Jika kosong, hapus semua WAF
                                $deployment->wafs()->delete();
                            }

                            // Update service_api_gates
                            if (!empty($deploymentData['service_api_gates'])) {
                                $serviceApiGateIds = [];
                                foreach ($deploymentData['service_api_gates'] as $serviceApiGateData) {
                                    if (!empty($serviceApiGateData['id'])) {
                                        $serviceApiGate = $deployment->serviceApiGates()->find($serviceApiGateData['id']);
                                        if ($serviceApiGate) {
                                            $serviceApiGate->update([
                                                'api_gate' => $serviceApiGateData['api_gate'] ?? '',
                                                'service_name' => $serviceApiGateData['service_name'] ?? '',
                                                'protocol' => $serviceApiGateData['protocol'] ?? '',
                                                'path' => $serviceApiGateData['path'] ?? '',
                                            ]);
                                        }
                                    } else {
                                        $serviceApiGate = $deployment->serviceApiGates()->create([
                                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                                            'api_gate' => $serviceApiGateData['api_gate'] ?? '',
                                            'service_name' => $serviceApiGateData['service_name'] ?? '',
                                            'protocol' => $serviceApiGateData['protocol'] ?? '',
                                            'path' => $serviceApiGateData['path'] ?? '',
                                        ]);
                                    }
                                    $serviceApiGateIds[] = $serviceApiGate->id;

                                    // Update route_api_gates
                                    if (!empty($serviceApiGateData['route_api_gates']) && is_array($serviceApiGateData['route_api_gates'])) {
                                        $routeApiGateIds = [];
                                        foreach ($serviceApiGateData['route_api_gates'] as $routeApiGateData) {
                                            // Skip jika data tidak valid atau ada masalah deep nesting
                                            if (!is_array($routeApiGateData) || empty($routeApiGateData['route_name']) || 
                                                is_string($routeApiGateData) || !isset($routeApiGateData['route_name'])) {
                                                continue;
                                            }
                                            
                                            if (!empty($routeApiGateData['id'])) {
                                                $routeApiGate = $serviceApiGate->routeApiGates()->find($routeApiGateData['id']);
                                                if ($routeApiGate) {
                                                    $routeApiGate->update([
                                                        'route_name' => $routeApiGateData['route_name'] ?? '',
                                                        'path' => $routeApiGateData['path'] ?? '',
                                                        'protocol' => $routeApiGateData['protocol'] ?? '',
                                                    ]);
                                                    $routeApiGateIds[] = $routeApiGate->id;
                                                }
                                            } else {
                                                $routeApiGate = $serviceApiGate->routeApiGates()->create([
                                                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                                                    'id_app' => $app->id,
                                                    'id_service_api_gate' => $serviceApiGate->id,
                                                    'route_name' => $routeApiGateData['route_name'] ?? '',
                                                    'path' => $routeApiGateData['path'] ?? '',
                                                    'protocol' => $routeApiGateData['protocol'] ?? '',
                                                ]);
                                                $routeApiGateIds[] = $routeApiGate->id;
                                            }
                                        }
                                        // Hapus route_api_gates yang tidak ada di request
                                        $serviceApiGate->routeApiGates()->whereNotIn('id', $routeApiGateIds)->delete();
                                    } else {
                                        // Jika kosong, hapus semua route_api_gates
                                        $serviceApiGate->routeApiGates()->delete();
                                    }
                                }
                                // Hapus service_api_gates yang tidak ada di request
                                $deployment->serviceApiGates()->whereNotIn('id', $serviceApiGateIds)->delete();
                            } else {
                                // Jika kosong, hapus semua service_api_gates
                                $deployment->serviceApiGates()->delete();
                            }
                        }
                        // Hapus deployments yang tidak ada di request
                        $project->deployments()->whereNotIn('id', $deploymentIds)->delete();
                    } else {
                        // Jika kosong, hapus semua deployments
                        $project->deployments()->delete();
                    }
                }
                // Hapus projects yang tidak ada di request
                $app->projects()->whereNotIn('id', $projectIds)->delete();
            } else {
                // Jika projects kosong, hapus semua project milik app ini
                $app->projects()->delete();
            }
            \DB::commit();
            $app->load('projects.deployments.dependencyPackages', 'projects.deployments.serviceApiGates.routeApiGates', 'projects.deployments.wafs.ippubliks.inpervas.dns');
            return response()->json($app, 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Gagal update aplikasi/relasi: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Menghapus aplikasi
    public function destroy($id)
    {
        try {
            $app = AppModel::findOrFail($id);
            $app->delete();
            return response()->json(['message' => 'Aplikasi dan seluruh relasinya berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
