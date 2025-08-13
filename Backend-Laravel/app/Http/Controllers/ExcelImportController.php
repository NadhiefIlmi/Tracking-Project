<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\App as AppModel;
use App\Models\Project;
use App\Models\Deployment;
use App\Models\DependencyPackage;
use App\Models\ServiceApiGate;
use App\Models\RouteApiGate;
use App\Models\Waf;
use App\Models\IpPublik;
use App\Models\Inperva;
use App\Models\Dns;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\XLSX\Options;
use Ramsey\Uuid\Uuid;

class ExcelImportController extends Controller
{
    public function showForm()
    {
        return view('import_excel');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx',
        ]);

        // Validasi file tidak kosong
        if (!$request->hasFile('excel_file') || !$request->file('excel_file')->isValid()) {
            return back()->with('error', 'File Excel tidak valid atau kosong');
        }

        $file = $request->file('excel_file');
        $filePath = $file->getPathname();

        $options = new Options();
        $reader = new Reader($options);
        $reader->open($filePath);

        $sheets = [];
        $sheetIndex = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            $sheets[$sheetIndex] = [];
            $rowIndex = 0;

            foreach ($sheet->getRowIterator() as $row) {
                if ($rowIndex === 0) {
                    $rowIndex++;
                    continue;
                }

                $rowData = $row->toArray();
                if (!empty($rowData) && isset($rowData[0])) {
                    $headers = $this->getHeadersForSheet($sheetIndex);
                    
                    // Pastikan jumlah elemen sama
                    if (count($headers) > 0) {
                        // Pad rowData jika lebih pendek dari headers
                        $paddedRowData = array_pad($rowData, count($headers), '');
                        // Trim jika rowData lebih panjang dari headers
                        $trimmedRowData = array_slice($paddedRowData, 0, count($headers));
                        
                        try {
                            $assocRow = array_combine($headers, $trimmedRowData);
                            $sheets[$sheetIndex][] = $assocRow;
                        } catch (\Exception $e) {
                            Log::warning("Failed to combine headers and row data", [
                                'sheetIndex' => $sheetIndex,
                                'headersCount' => count($headers),
                                'rowDataCount' => count($rowData),
                                'trimmedRowDataCount' => count($trimmedRowData),
                                'headers' => $headers,
                                'rowData' => $rowData
                            ]);
                            // Skip this row if array_combine fails
                            continue;
                                }
    }


}
                $rowIndex++;
            }
            $sheetIndex++;
        }

        $reader->close();

        // Validasi bahwa sheets tidak kosong
        if (empty($sheets)) {
            return back()->with('error', 'File Excel tidak memiliki data yang valid');
        }

        try {
            DB::transaction(function() use ($sheets) {
                // Create mapping arrays to track relationships
                $appIdMap = [];
                $projectIdMap = [];
                $deploymentIdMap = [];

                // 1. Import apps first
                $this->importApps($sheets, $appIdMap);
                
                // 2. Import projects with validation
                $this->importProjects($sheets, $appIdMap, $projectIdMap);
                
                // 3. Import deployments with validation
                $this->importDeployments($sheets, $projectIdMap, $deploymentIdMap);
                
                // 4. Import dependency packages
                $this->importDependencyPackages($sheets, $deploymentIdMap);
                
                // 5. Import service api gates
                $serviceApiGateIdMap = [];
                $this->importServiceApiGates($sheets, $deploymentIdMap, $serviceApiGateIdMap);
                
                // 6. Import route api gates
                $this->importRouteApiGates($sheets, $appIdMap, $serviceApiGateIdMap);
                
                // 7. Import wafs
                $wafIdMap = [];
                $this->importWafs($sheets, $deploymentIdMap, $wafIdMap);
                
                // 8. Import ip publiks
                $ipPublikIdMap = [];
                $this->importIpPubliks($sheets, $wafIdMap, $ipPublikIdMap);
                
                // 9. Import inpervas
                $inpervaIdMap = [];
                $this->importInpervas($sheets, $ipPublikIdMap, $inpervaIdMap);
                
                // 10. Import dns
                $this->importDns($sheets, $inpervaIdMap);
            });

            return back()->with('success', 'Import berhasil!');
        } catch (\Exception $e) {
            Log::error('Excel import failed: ' . $e->getMessage());
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    protected function getHeadersForSheet($sheetIndex)
    {
        $headers = [
            0 => ['id', 'proses_bisnis', 'nama_singkat_proses_bisnis', 'unit_kerja_pemilik_proses_bisnis', 'jenis_layanan', 'nama_aplikasi', 'penanggung_jawab_aplikasi'],
            1 => ['id', 'id_app', 'project_name', 'base_project', 'fungsi', 'technology', 'framework', 'version'],
            2 => ['id', 'id_project', 'namespace', 'service_name', 'type', 'cluster_ip', 'external_ip', 'port', 'jenis_app', 'tahap', 'env', 'nginx', 'php'],
            3 => ['id', 'id_deployment', 'dependency_name', 'version'],
            4 => ['id', 'id_deployment', 'api_gate', 'service_name', 'protocol', 'path'],
            5 => ['id', 'id_app', 'id_service_api-gate', 'route_name', 'path', 'protocol'],
            6 => ['id', 'id_deployment', 'ip_waf'],
            7 => ['id', 'id_waf', 'ip_publik'],
            8 => ['id', 'id_ip_publik', 'name'],
            9 => ['id', 'id_inperva', 'name', 'type'],
        ];

        return $headers[$sheetIndex] ?? [];
    }

    protected function importApps(&$sheets, &$idMap)
    {
        if (!isset($sheets[0])) return;

        foreach ($sheets[0] as $row) {
            if (empty($row['proses_bisnis'])) continue;
            $app = AppModel::create([
                'proses_bisnis' => $row['proses_bisnis'] ?? '',
                'nama_singkat_proses_bisnis' => $row['nama_singkat_proses_bisnis'] ?? '',
                'unit_kerja_pemilik_proses_bisnis' => $row['unit_kerja_pemilik_proses_bisnis'] ?? '',
                'jenis_layanan' => $row['jenis_layanan'] ?? '',
                'nama_aplikasi' => $row['nama_aplikasi'] ?? '',
                'penanggung_jawab_aplikasi' => $row['penanggung_jawab_aplikasi'] ?? '',
            ]);
            $idMap['app'][$row['id']] = $app->id;
        }
    }

    protected function importProjects(&$sheets, $appIdMap, &$projectIdMap)
    {
        if (!isset($sheets[1])) return;

        foreach ($sheets[1] as $row) {
            if (empty($row['project_name'])) continue;

            // Get the mapped app ID
            $appId = $appIdMap['app'][$row['id_app']] ?? null;

            if (!$appId) {
                Log::warning("Project {$row['id']} skipped: Referenced app {$row['id_app']} not found");
                continue;
            }

            $project = Project::create([
                'id_app' => $appId,
                'project_name' => $row['project_name'] ?? '',
                'base_project' => $row['base_project'] ?? '',
                'fungsi' => $row['fungsi'] ?? '',
                'technology' => $row['technology'] ?? '',
                'framework' => $row['framework'] ?? '',
                'version' => $row['version'] ?? '',
            ]);

            // Map the Excel ID to the database ID
            $projectIdMap[$row['id']] = $project->id;
        }
    }

    protected function importDeployments(&$sheets, $projectIdMap, &$deploymentIdMap)
    {
        if (!isset($sheets[2])) return;

        foreach ($sheets[2] as $row) {
            if (empty($row['namespace'])) continue;

            // Get the mapped project ID
            $projectId = $projectIdMap[$row['id_project']] ?? null;

            if (!$projectId) {
                Log::warning("Deployment {$row['id']} skipped: Referenced project {$row['id_project']} not found");
                continue;
            }

            $deployment = Deployment::create([
                'id_project' => $projectId,
                'namespace' => $row['namespace'] ?? '',
                'service_name' => $row['service_name'] ?? '',
                'type' => $row['type'] ?? '',
                'cluster_ip' => $row['cluster_ip'] ?? '',
                'external_ip' => $row['external_ip'] ?? '',
                'port' => $row['port'] ?? '',
                'jenis_app' => $row['jenis_app'] ?? '',
                'tahap' => $row['tahap'] ?? '',
                'env' => $row['env'] ?? '',
            ]);

            // Map the Excel ID to the database ID
            $deploymentIdMap[$row['id']] = $deployment->id;
        }
    }

    protected function importDependencyPackages(&$sheets, $deploymentIdMap)
    {
        if (!isset($sheets[3])) return;

        foreach ($sheets[3] as $row) {
            if (empty($row['dependency_name'])) continue;

            // Get the mapped deployment ID
            $deploymentId = $deploymentIdMap[$row['id_deployment']] ?? null;

            if (!$deploymentId) {
                Log::warning("Dependency package {$row['id']} skipped: Referenced deployment {$row['id_deployment']} not found");
                continue;
            }

            DependencyPackage::create([
                'id_deployment' => $deploymentId,
                'dependency_name' => $row['dependency_name'] ?? '',
                'version' => $row['version'] ?? '',
            ]);
        }
    }

    protected function importServiceApiGates(&$sheets, $deploymentIdMap, &$serviceApiGateIdMap)
    {
        if (!isset($sheets[4])) return;

        foreach ($sheets[4] as $row) {
            if (empty($row['api_gate'])) continue;

            // Get the mapped deployment ID
            $deploymentId = $deploymentIdMap[$row['id_deployment']] ?? null;

            if (!$deploymentId) {
                Log::warning("Service API gate {$row['id']} skipped: Referenced deployment {$row['id_deployment']} not found");
                continue;
            }

            // Handle UUID for service_api_gates
            $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();

            ServiceApiGate::create([
                'id' => $uuid,
                'id_deployment' => $deploymentId,
                'api_gate' => $row['api_gate'] ?? '',
                'service_name' => $row['service_name'] ?? '',
                'protocol' => $row['protocol'] ?? '',
                'path' => $row['path'] ?? '',
            ]);
            $serviceApiGateIdMap[$row['id']] = $uuid;
        }
    }

    protected function importRouteApiGates(&$sheets, $appIdMap, $serviceApiGateIdMap)
    {
        if (!isset($sheets[5])) return;

        foreach ($sheets[5] as $row) {
            if (empty($row['route_name'])) continue;

            // Get the mapped app ID
            $appId = $appIdMap['app'][$row['id_app']] ?? null;

            if (!$appId) {
                Log::warning("Route API gate {$row['id']} skipped: Referenced app {$row['id_app']} not found");
                continue;
            }

            // Handle UUID for route_api_gates
            $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();

            $serviceApiGateUuid = $serviceApiGateIdMap[$row['id_service_api-gate']] ?? null;

            if (!$serviceApiGateUuid) {
                Log::warning("Route API gate {$row['id']} skipped: Referenced service API gate {$row['id_service_api-gate']} not found");
                continue;
            }

            RouteApiGate::create([
                'id' => $uuid,
                'id_app' => $appId,
                'id_service_api_gate' => $serviceApiGateUuid,
                'route_name' => $row['route_name'] ?? '',
                'path' => $row['path'] ?? '',
                'protocol' => $row['protocol'] ?? '',
            ]);
        }
    }

    protected function importWafs(&$sheets, $deploymentIdMap, &$wafIdMap)
    {
        if (!isset($sheets[6])) return;

        foreach ($sheets[6] as $row) {
            if (empty($row['ip_waf'])) continue;

            // Get the mapped deployment ID
            $deploymentId = $deploymentIdMap[$row['id_deployment']] ?? null;

            if (!$deploymentId) {
                Log::warning("WAF {$row['id']} skipped: Referenced deployment {$row['id_deployment']} not found");
                continue;
            }

            $waf = Waf::create([
                'id_deployment' => $deploymentId,
                'ip_waf' => $row['ip_waf'] ?? '',
            ]);

            // Map the Excel ID to the database ID
            $wafIdMap[$row['id']] = $waf->id;
        }
    }

    protected function importIpPubliks(&$sheets, $wafIdMap, &$ipPublikIdMap)
    {
        if (!isset($sheets[7])) return;

        foreach ($sheets[7] as $row) {
            if (empty($row['ip_publik'])) continue;

            // Get the mapped waf ID
            $wafId = $wafIdMap[$row['id_waf']] ?? null;

            if (!$wafId) {
                Log::warning("IP Publik {$row['id']} skipped: Referenced WAF {$row['id_waf']} not found");
                continue;
            }

            $ipPublik = IpPublik::create([
                'id_waf' => $wafId,
                'ip_publik' => $row['ip_publik'] ?? '',
            ]);

            // Map the Excel ID to the database ID
            $ipPublikIdMap[$row['id']] = $ipPublik->id;
        }
    }

    protected function importInpervas(&$sheets, $ipPublikIdMap, &$inpervaIdMap)
    {
        if (!isset($sheets[8])) return;

        foreach ($sheets[8] as $row) {
            if (empty($row['name'])) continue;

            // Get the mapped ip publik ID
            $ipPublikId = $ipPublikIdMap[$row['id_ip_publik']] ?? null;

            if (!$ipPublikId) {
                Log::warning("Inperva {$row['id']} skipped: Referenced IP Publik {$row['id_ip_publik']} not found");
                continue;
            }

            $inperva = Inperva::create([
                'id_ip_publik' => $ipPublikId,
                'name' => $row['name'] ?? '',
            ]);

            // Map the Excel ID to the database ID
            $inpervaIdMap[$row['id']] = $inperva->id;
        }
    }

    protected function importDns(&$sheets, $inpervaIdMap)
    {
        if (!isset($sheets[9])) return;

        foreach ($sheets[9] as $row) {
            if (empty($row['name'])) continue;

            // Get the mapped inperva ID
            $inpervaId = $inpervaIdMap[$row['id_inperva']] ?? null;

            if (!$inpervaId) {
                Log::warning("DNS {$row['id']} skipped: Referenced Inperva {$row['id_inperva']} not found");
                continue;
            }

            Dns::create([
                'id_inperva' => $inpervaId,
                'name' => $row['name'] ?? '',
                'type' => $row['type'] ?? '',
            ]);
        }
    }
}