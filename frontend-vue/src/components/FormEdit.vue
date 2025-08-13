<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-4xl relative max-h-[90vh] flex flex-col h-[500px]">
      <!-- Header with breadcrumb -->
      <div class="flex justify-between items-center mb-6 border-b pb-4">
        <div class="flex items-center space-x-2">
          <template v-for="(step, index) in steps" :key="index">
            <button
              type="button"
              @click="goToStep(index)"
              class="flex items-center focus:outline-none"
              :class="{'font-bold text-blue-600 underline': currentStep === index, 'text-gray-500': currentStep !== index}"
              :disabled="currentStep === index"
            >
              <span class="hidden sm:inline">{{ step.title }}</span>
            </button>
            <span v-if="index < steps.length - 1" class="mx-2 text-gray-400 hidden sm:inline">/</span>
          </template>
        </div>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <!-- Form container with slide/step -->
      <div class="flex-1 overflow-y-auto mb-6">
        <!-- Step 1: App Information -->
        <div v-show="currentStep === 0" class="space-y-4">
          <h2 class="text-xl font-bold mb-4">Aplikasi</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium mb-1">Proses Bisnis <span class='text-red-500'>*</span></label>
              <input v-model="form.proses_bisnis" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block font-medium mb-1">Nama Singkat Proses Bisnis <span class='text-red-500'>*</span></label>
              <input v-model="form.nama_singkat_proses_bisnis" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block font-medium mb-1">Unit Kerja Pemilik <span class='text-red-500'>*</span></label>
              <input v-model="form.unit_kerja_pemilik_proses_bisnis" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block font-medium mb-1">Jenis Layanan <span class='text-red-500'>*</span></label>
              <input v-model="form.jenis_layanan" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block font-medium mb-1">Nama Aplikasi <span class='text-red-500'>*</span></label>
              <input v-model="form.nama_aplikasi" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block font-medium mb-1">Penanggung Jawab <span class='text-red-500'>*</span></label>
              <input v-model="form.penanggung_jawab_aplikasi" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
          </div>
        </div>

        <!-- Step 2: Projects -->
        <div v-show="currentStep === 1" class="space-y-4">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold">Project</h2>
            <button type="button" @click="addProject" class="px-3 py-1 bg-green-500 text-white rounded text-sm">
              + Tambah Project
            </button>
          </div>
          <div class="space-y-4 mt-4">
            <div v-for="(project, pIdx) in form.projects" :key="project.id || pIdx" class="border rounded-lg p-4 bg-gray-50">
              <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold">Project {{ pIdx + 1 }}</h3>
                <button type="button" @click="removeProject(pIdx)" class="text-red-500 text-sm">
                  Hapus Project
                </button>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block font-medium mb-1">Nama Project</label>
                  <input v-model="project.project_name" class="w-full border rounded px-3 py-2" type="text" required />
                </div>
                <div>
                  <label class="block font-medium mb-1">Base Project</label>
                  <input v-model="project.base_project" class="w-full border rounded px-3 py-2" type="text" required />
                </div>
                <div>
                  <label class="block font-medium mb-1">Fungsi</label>
                  <input v-model="project.fungsi" class="w-full border rounded px-3 py-2" type="text" required />
                </div>
                <div>
                  <label class="block font-medium mb-1">Technology</label>
                  <input v-model="project.technology" class="w-full border rounded px-3 py-2" type="text" required />
                </div>
                <div>
                  <label class="block font-medium mb-1">Framework</label>
                  <input v-model="project.framework" class="w-full border rounded px-3 py-2" type="text" required />
                </div>
                <div>
                  <label class="block font-medium mb-1">Version</label>
                  <input v-model="project.version" class="w-full border rounded px-3 py-2" type="text" required />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Deployments -->
        <div v-show="currentStep === 2" class="space-y-4">
          <h2 class="text-xl font-bold mb-4">Deployment</h2>
          <div class="space-y-6">
            <div v-for="(project, pIdx) in form.projects" :key="project.id || pIdx" class="border rounded-lg p-4 bg-gray-50">
              <h3 class="text-lg font-bold text-blue-700 mb-3">Project: {{ project.project_name || 'Untitled Project' }}</h3>
              <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium">Deployment</h4>
                <button type="button" @click="addDeployment(pIdx)" class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                  + Tambah Deployment
                </button>
              </div>
              <div class="space-y-4">
                <div v-for="(deployment, dIdx) in project.deployments" :key="deployment.id || dIdx" class="border rounded p-4 bg-white">
                  <div class="flex justify-between items-center mb-3">
                    <h4 class="font-medium">Deployment {{ dIdx + 1 }}</h4>
                    <button type="button" @click="removeDeployment(pIdx, dIdx)" class="text-red-500 text-sm">
                      Hapus Deployment
                    </button>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block font-medium mb-1">Namespace</label>
                      <input v-model="deployment.namespace" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">Service Name</label>
                      <input v-model="deployment.service_name" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">Type</label>
                      <input v-model="deployment.type" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">Cluster IP</label>
                      <input v-model="deployment.cluster_ip" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">External IP</label>
                      <input v-model="deployment.external_ip" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">Port</label>
                      <input v-model="deployment.port" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">Environment</label>
                      <input v-model="deployment.env" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">Jenis App</label>
                      <input v-model="deployment.jenis_app" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    <div>
                      <label class="block font-medium mb-1">Tahap</label>
                      <input v-model="deployment.tahap" class="w-full border rounded px-3 py-2" type="text" />
                    </div>
                    
                    <!-- Dependency Packages Section -->
                    <div class="col-span-2">
                      <div class="flex justify-between items-center mb-3">
                        <label class="block font-medium mb-1">Dependency Packages</label>
                        <button type="button" @click="addDependencyPackage(pIdx, dIdx)" class="px-3 py-1 bg-orange-500 text-white rounded text-sm">
                          + Tambah Dependency
                        </button>
                      </div>
                      <div class="space-y-3">
                        <div v-for="(dependency, depIdx) in deployment.dependency_packages" :key="dependency.id || depIdx" class="border rounded p-3 bg-gray-50">
                          <div class="flex justify-between items-center mb-2">
                            <h6 class="text-sm font-medium text-orange-700">Dependency {{ depIdx + 1 }}</h6>
                            <button type="button" @click="removeDependencyPackage(pIdx, dIdx, depIdx)" class="text-red-500 text-sm">
                              Hapus
                            </button>
                          </div>
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                              <label class="block text-xs font-medium mb-1">Dependency Name</label>
                              <input v-model="dependency.dependency_name" placeholder="Nama Dependency" class="w-full border rounded px-2 py-1 text-sm" />
                            </div>
                            <div>
                              <label class="block text-xs font-medium mb-1">Version</label>
                              <input v-model="dependency.version" placeholder="Versi" class="w-full border rounded px-2 py-1 text-sm" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Service API Gates -->
        <div v-show="currentStep === 3" class="space-y-4">
          <h2 class="text-xl font-bold mb-4">Service API Gates</h2>
          <div class="space-y-6">
            <div v-for="(project, pIdx) in form.projects" :key="project.id || pIdx" class="border rounded-lg p-4 bg-gray-50">
              <h3 class="font-semibold mb-3">Project: {{ project.project_name || 'Untitled Project' }}</h3>
              <div v-for="(deployment, dIdx) in project.deployments" :key="deployment.id || dIdx" class="mb-6">
                <h4 class="text-base font-semibold text-green-700 mb-3">Deployment: {{ deployment.service_name || 'Untitled Deployment' }}</h4>
                <div class="flex justify-between items-center mb-3">
                  <h5 class="font-medium">Service API Gate</h5>
                  <button type="button" @click="addServiceApiGate(pIdx, dIdx)" class="px-3 py-1 bg-purple-500 text-white rounded text-sm">
                    + Tambah Service API Gate
                  </button>
                </div>
                <div class="space-y-4">
                  <div v-for="(serviceApiGate, sIdx) in deployment.service_api_gates" :key="serviceApiGate.id || sIdx" class="border rounded p-4 bg-white">
                    <div class="flex justify-between items-center mb-3">
                      <h5 class="font-medium">Service API Gate {{ sIdx + 1 }}</h5>
                      <button type="button" @click="removeServiceApiGate(pIdx, dIdx, sIdx)" class="text-red-500 text-sm">
                        Hapus Service
                      </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block font-medium mb-1">API Gate</label>
                        <input v-model="serviceApiGate.api_gate" placeholder="API Gate" class="w-full border rounded px-3 py-2" />
                      </div>
                      <div>
                        <label class="block font-medium mb-1">Service Name</label>
                        <input v-model="serviceApiGate.service_name" placeholder="Service Name" class="w-full border rounded px-3 py-2" />
                      </div>
                      <div>
                        <label class="block font-medium mb-1">Protocol</label>
                        <input v-model="serviceApiGate.protocol" placeholder="Protocol" class="w-full border rounded px-3 py-2" />
                      </div>
                      <div>
                        <label class="block font-medium mb-1">Path</label>
                        <input v-model="serviceApiGate.path" placeholder="Path" class="w-full border rounded px-3 py-2" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 5: Route API Gates -->
        <div v-show="currentStep === 4" class="space-y-4">
          <h2 class="text-xl font-bold mb-4">Route API Gates</h2>
          <div class="space-y-6">
            <div v-for="(project, pIdx) in form.projects" :key="project.id || pIdx" class="border rounded-lg p-4 bg-gray-50">
              <h3 class="font-semibold mb-3">Project: {{ project.project_name || 'Untitled Project' }}</h3>
              <div v-for="(deployment, dIdx) in project.deployments" :key="deployment.id || dIdx" class="mb-6">
                <h4 class="font-medium mb-3">Deployment: {{ deployment.service_name || 'Untitled Deployment' }}</h4>
                <div v-for="(serviceApiGate, sIdx) in deployment.service_api_gates" :key="serviceApiGate.id || sIdx" class="mb-6">
                  <h5 class="text-base font-semibold text-purple-700 mb-3">Service API Gate: {{ serviceApiGate.service_name || 'Untitled Service' }}</h5>
                  <div class="flex justify-between items-center mb-3">
                    <h6 class="font-medium">Route API Gate</h6>
                    <button type="button" @click="addRouteApiGate(pIdx, dIdx, sIdx)" class="px-3 py-1 bg-indigo-500 text-white rounded text-sm">
                      + Tambah Route API Gate
                    </button>
                  </div>
                  <div class="space-y-4">
                    <div v-for="(routeApiGate, rIdx) in serviceApiGate.route_api_gates" :key="routeApiGate.id || rIdx" class="border rounded p-4 bg-white">
                      <div class="flex justify-between items-center mb-3">
                        <h6 class="text-base font-medium text-indigo-700 mb-3">Route API Gate {{ rIdx + 1 }}</h6>
                        <button type="button" @click="removeRouteApiGate(pIdx, dIdx, sIdx, rIdx)" class="text-red-500 text-sm">
                          Hapus Route
                        </button>
                      </div>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block font-medium mb-1">Route Name</label>
                          <input v-model="routeApiGate.route_name" placeholder="Route Name" class="w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                          <label class="block font-medium mb-1">Path</label>
                          <input v-model="routeApiGate.path" placeholder="Path" class="w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                          <label class="block font-medium mb-1">Protocol</label>
                          <input v-model="routeApiGate.protocol" placeholder="Protocol" class="w-full border rounded px-3 py-2" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 6: Infrastructure (WAF, IP Publik, Inperva, DNS) -->
        <div v-show="currentStep === 5" class="space-y-4">
          <h2 class="text-xl font-bold mb-4">Infrastructure</h2>
          
          <div class="space-y-6">
            <div v-for="(project, pIdx) in form.projects" :key="project.id || pIdx" class="border rounded-lg p-4 bg-gray-50">
              <h3 class="font-semibold mb-3">Project: {{ project.project_name || 'Untitled Project' }}</h3>
              
              <div v-for="(deployment, dIdx) in project.deployments" :key="deployment.id || dIdx" class="mb-6">
                <h4 class="text-base font-semibold text-red-700 mb-3">Deployment: {{ deployment.service_name || 'Untitled Deployment' }}</h4>
                
                <div class="flex justify-between items-center mb-3">
                  <h5 class="font-medium">WAF & Infrastructure</h5>
                  <button type="button" @click="addWaf(pIdx, dIdx)" class="px-3 py-1 bg-red-500 text-white rounded text-sm">
                    + Tambah WAF
                  </button>
                </div>
                
                <div class="space-y-4">
                  <div v-for="(waf, wIdx) in deployment.wafs" :key="waf.id || wIdx" class="border rounded p-4 bg-white">
                    <div class="flex justify-between items-center mb-3">
                      <h5 class="font-medium text-red-600">WAF {{ wIdx + 1 }}</h5>
                      <button type="button" @click="removeWaf(pIdx, dIdx, wIdx)" class="text-red-500 text-sm">
                        Hapus WAF
                      </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                      <div>
                        <label class="block font-medium mb-1">IP WAF</label>
                        <input v-model="waf.ip_waf" placeholder="IP WAF" class="w-full border rounded px-3 py-2" />
                      </div>
                    </div>
                    
                    <!-- IP Publik Section -->
                    <div class="mb-4">
                      <div class="flex justify-between items-center mb-3">
                        <label class="block font-medium mb-1">IP Publik</label>
                        <button type="button" @click="addIpPublik(pIdx, dIdx, wIdx)" class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                          + Tambah IP Publik
                        </button>
                      </div>
                      <div class="space-y-3">
                        <div v-for="(ipPublik, ipIdx) in waf.ippubliks" :key="ipPublik.id || ipIdx" class="border rounded p-3 bg-gray-50">
                          <div class="flex justify-between items-center mb-2">
                            <h6 class="text-sm font-medium text-blue-700">IP Publik {{ ipIdx + 1 }}</h6>
                            <button type="button" @click="removeIpPublik(pIdx, dIdx, wIdx, ipIdx)" class="text-red-500 text-sm">
                              Hapus
                            </button>
                          </div>
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <div>
                              <label class="block text-xs font-medium mb-1">IP Publik</label>
                              <input v-model="ipPublik.ip_publik" placeholder="IP Publik" class="w-full border rounded px-2 py-1 text-sm" />
                            </div>
                          </div>
                          
                          <!-- Inperva Section -->
                          <div class="mb-3">
                            <div class="flex justify-between items-center mb-2">
                              <label class="block text-xs font-medium mb-1">Inperva</label>
                              <button type="button" @click="addInperva(pIdx, dIdx, wIdx, ipIdx)" class="px-2 py-1 bg-green-500 text-white rounded text-xs">
                                + Tambah Inperva
                              </button>
                            </div>
                            <div class="space-y-2">
                              <div v-for="(inperva, inpIdx) in ipPublik.inpervas" :key="inperva.id || inpIdx" class="border rounded p-2 bg-white">
                                <div class="flex justify-between items-center mb-2">
                                  <h6 class="text-xs font-medium text-green-700">Inperva {{ inpIdx + 1 }}</h6>
                                  <button type="button" @click="removeInperva(pIdx, dIdx, wIdx, ipIdx, inpIdx)" class="text-red-500 text-xs">
                                    Hapus
                                  </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                                  <div>
                                    <label class="block text-xs font-medium mb-1">Nama</label>
                                    <input v-model="inperva.name" placeholder="Nama Inperva" class="w-full border rounded px-2 py-1 text-xs" />
                                  </div>
                                </div>
                                
                                <!-- DNS Section -->
                                <div class="mb-2">
                                  <div class="flex justify-between items-center mb-2">
                                    <label class="block text-xs font-medium mb-1">DNS</label>
                                    <button type="button" @click="addDns(pIdx, dIdx, wIdx, ipIdx, inpIdx)" class="px-2 py-1 bg-purple-500 text-white rounded text-xs">
                                      + Tambah DNS
                                    </button>
                                  </div>
                                  <div class="space-y-2">
                                    <div v-for="(dns, dnsIdx) in inperva.dns" :key="dns.id || dnsIdx" class="border rounded p-2 bg-gray-50">
                                      <div class="flex justify-between items-center mb-2">
                                        <h6 class="text-xs font-medium text-purple-700">DNS {{ dnsIdx + 1 }}</h6>
                                        <button type="button" @click="removeDns(pIdx, dIdx, wIdx, ipIdx, inpIdx, dnsIdx)" class="text-red-500 text-xs">
                                          Hapus
                                        </button>
                                      </div>
                                      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div>
                                          <label class="block font-medium mb-1">Nama</label>
                                          <input v-model="dns.name" placeholder="Nama DNS" class="w-full border rounded px-2 py-1 text-xs" />
                                        </div>
                                        <div>
                                          <label class="block font-medium mb-1">Type</label>
                                          <input v-model="dns.type" placeholder="Type DNS" class="w-full border rounded px-2 py-1 text-xs" />
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation buttons -->
      <div class="flex justify-between border-t pt-4">
        <button type="button" 
                @click="prevStep" 
                :disabled="currentStep === 0"
                class="px-4 py-2 rounded border bg-white text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
          Kembali
        </button>
        <div v-if="currentStep < steps.length - 1">
          <button type="button" 
                  @click="nextStep" 
                  class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 font-medium">
            Lanjut
          </button>
        </div>
        <div v-else>
          <button type="button" 
                  @click="handleSubmit" 
                  :disabled="loading"
                  class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 font-medium">
            {{ loading ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, nextTick } from 'vue';

const props = defineProps({
  app: { type: Object, required: true }
});
const emit = defineEmits(['close', 'saved', 'notification']);

const steps = [
  { title: 'Aplikasi' },
  { title: 'Project' },
  { title: 'Deployment' },
  { title: 'Service API' },
  { title: 'Route API' },
  { title: 'Infrastructure' }
];
const currentStep = ref(0);

function goToStep(idx) {
  currentStep.value = idx;
}
function nextStep() {
  if (currentStep.value < steps.length - 1) currentStep.value++;
}
function prevStep() {
  if (currentStep.value > 0) currentStep.value--;
}

function ensureArray(obj, fallback = []) {
  return Array.isArray(obj) ? obj : fallback;
}

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });

function showNotification(message, type = 'success') {
  console.log('showNotification called:', message, type);
  // Reset notification state first
  notification.value = { show: false, message: '', type: 'success' };
  
  // Use nextTick to ensure DOM update
  nextTick(() => {
    console.log('Setting notification to show:', message, type);
    notification.value = { show: true, message, type };
    setTimeout(() => {
      notification.value.show = false;
    }, 3000);
  });
}

const form = reactive({
  id: props.app.id,
  proses_bisnis: props.app.proses_bisnis || '',
  nama_singkat_proses_bisnis: props.app.nama_singkat_proses_bisnis || '',
  unit_kerja_pemilik_proses_bisnis: props.app.unit_kerja_pemilik_proses_bisnis || '',
  jenis_layanan: props.app.jenis_layanan || '',
  nama_aplikasi: props.app.nama_aplikasi || '',
  penanggung_jawab_aplikasi: props.app.penanggung_jawab_aplikasi || '',
  projects: ensureArray(props.app.projects).map(p => ({
    ...p,
    dependency_packages: ensureArray(p.dependency_packages, [{}]),
    deployments: ensureArray(p.deployments).map(d => ({
      ...d,
      dependency_packages: ensureArray(d.dependency_packages, []),
      service_api_gates: ensureArray(d.service_api_gates).map(s => ({
        ...s,
        route_api_gates: ensureArray(s.route_api_gates, [{}])
      })),
      wafs: ensureArray(d.wafs).map(w => ({
        ...w,
        ippubliks: ensureArray(w.ippubliks).map(ip => ({
          ...ip,
          inpervas: ensureArray(ip.inpervas).map(inp => ({
            ...inp,
            dns: ensureArray(inp.dns, [{}])
          }))
        }))
      }))
    }))
  }))
});

// Project
function addProject() {
  form.projects.push({
    project_name: '', base_project: '', fungsi: '', technology: '', framework: '', version: '',
    dependency_packages: [{ dependency_name: '', version: '' }],
    deployments: []
  });
}
function removeProject(idx) { form.projects.splice(idx, 1); }

// Deployment
function addDeployment(pIdx) {
  form.projects[pIdx].deployments.push({
    namespace: '', service_name: '', type: '', cluster_ip: '', external_ip: '', port: '', 
    jenis_app: '', tahap: '', env: '',
    dependency_packages: [],
    service_api_gates: [],
    wafs: []
  });
}
function removeDeployment(pIdx, depIdx) {
  form.projects[pIdx].deployments.splice(depIdx, 1);
}

// Service API Gate
function addServiceApiGate(pIdx, depIdx) {
  form.projects[pIdx].deployments[depIdx].service_api_gates.push({
    api_gate: '', service_name: '', protocol: '', path: '', 
    route_api_gates: []
  });
}
function removeServiceApiGate(pIdx, depIdx, sIdx) {
  form.projects[pIdx].deployments[depIdx].service_api_gates.splice(sIdx, 1);
}

// Route API Gate
function addRouteApiGate(pIdx, depIdx, sIdx) {
  form.projects[pIdx].deployments[depIdx].service_api_gates[sIdx].route_api_gates.push({ 
    route_name: '', path: '', protocol: '' 
  });
}
function removeRouteApiGate(pIdx, depIdx, sIdx, rIdx) {
  form.projects[pIdx].deployments[depIdx].service_api_gates[sIdx].route_api_gates.splice(rIdx, 1);
}

// Dependency Package
function addDependencyPackage(pIdx, dIdx) {
  if (!form.projects[pIdx].deployments[dIdx].dependency_packages) {
    form.projects[pIdx].deployments[dIdx].dependency_packages = [];
  }
  form.projects[pIdx].deployments[dIdx].dependency_packages.push({
    dependency_name: '',
    version: ''
  });
}
function removeDependencyPackage(pIdx, dIdx, depIdx) {
  form.projects[pIdx].deployments[dIdx].dependency_packages.splice(depIdx, 1);
}

// WAF
function addWaf(pIdx, dIdx) {
  if (!form.projects[pIdx].deployments[dIdx].wafs) {
    form.projects[pIdx].deployments[dIdx].wafs = [];
  }
  form.projects[pIdx].deployments[dIdx].wafs.push({
    ip_waf: '',
    ippubliks: [{ ip_publik: '', inpervas: [{ name: '', dns: [{ name: '', type: '' }] }] }]
  });
}
function removeWaf(pIdx, dIdx, wIdx) {
  form.projects[pIdx].deployments[dIdx].wafs.splice(wIdx, 1);
}

// IP Publik
function addIpPublik(pIdx, dIdx, wIdx) {
  if (!form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks) {
    form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks = [];
  }
  form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks.push({ ip_publik: '' });
}
function removeIpPublik(pIdx, dIdx, wIdx, ipIdx) {
  form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks.splice(ipIdx, 1);
}

// Inperva
function addInperva(pIdx, dIdx, wIdx, ipIdx) {
  if (!form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas) {
    form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas = [];
  }
  form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas.push({ name: '' });
}
function removeInperva(pIdx, dIdx, wIdx, ipIdx, inpIdx) {
  form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas.splice(inpIdx, 1);
}

// DNS
function addDns(pIdx, dIdx, wIdx, ipIdx, inpIdx) {
  if (!form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas[inpIdx].dns) {
    form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas[inpIdx].dns = [];
  }
  form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas[inpIdx].dns.push({ name: '', type: '' });
}
function removeDns(pIdx, dIdx, wIdx, ipIdx, inpIdx, dnsIdx) {
  form.projects[pIdx].deployments[dIdx].wafs[wIdx].ippubliks[ipIdx].inpervas[inpIdx].dns.splice(dnsIdx, 1);
}

async function handleSubmit() {
  loading.value = true;
  try {
    const payload = {
      nama_singkat_proses_bisnis: form.nama_singkat_proses_bisnis,
      proses_bisnis: form.proses_bisnis,
      unit_kerja_pemilik_proses_bisnis: form.unit_kerja_pemilik_proses_bisnis,
      jenis_layanan: form.jenis_layanan,
      nama_aplikasi: form.nama_aplikasi,
      penanggung_jawab_aplikasi: form.penanggung_jawab_aplikasi,
      projects: form.projects.map(p => ({
        id: p.id,
        project_name: p.project_name,
        base_project: p.base_project,
        fungsi: p.fungsi,
        technology: p.technology,
        framework: p.framework,
        version: p.version,
        dependency_package_name: p.dependency_packages && p.dependency_packages[0] ? p.dependency_packages[0].dependency_name : '',
        dependency_package_version: p.dependency_packages && p.dependency_packages[0] ? p.dependency_packages[0].version : '',
        deployments: p.deployments ? p.deployments.map(d => ({
          id: d.id,
          namespace: d.namespace,
          service_name: d.service_name,
          type: d.type,
          cluster_ip: d.cluster_ip,
          external_ip: d.external_ip,
          port: d.port,
          jenis_app: d.jenis_app,
          tahap: d.tahap,
          env: d.env,
          dependency_packages: d.dependency_packages ? d.dependency_packages.map(dp => ({
            id: dp.id,
            dependency_name: dp.dependency_name,
            version: dp.version
          })) : [],
          service_api_gates: d.service_api_gates ? d.service_api_gates.map(s => ({
            id: s.id,
            api_gate: s.api_gate,
            service_name: s.service_name,
            protocol: s.protocol,
            path: s.path,
            route_api_gates: s.route_api_gates ? s.route_api_gates.map(r => ({
              id: r.id,
              route_name: r.route_name,
              path: r.path,
              protocol: r.protocol
            })) : []
          })) : [],
          wafs: d.wafs ? d.wafs.map(w => ({
            id: w.id,
            ip_waf: w.ip_waf,
            ippubliks: w.ippubliks ? w.ippubliks.map(ip => ({
              id: ip.id,
              ip_publik: ip.ip_publik,
              inpervas: ip.inpervas ? ip.inpervas.map(inp => ({
                id: inp.id,
                name: inp.name,
                dns: inp.dns ? inp.dns.map(dns => ({
                  id: dns.id,
                  name: dns.name,
                  type: dns.type
                })) : []
              })) : []
            })) : []
          })) : []
        })) : []
      }))
    };

    const res = await fetch(`http://localhost:8000/api/aplikasi/${form.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    if (!res.ok) {
      const errorData = await res.json();
      throw new Error(errorData.message || 'Gagal menyimpan aplikasi');
    }
    
    // Notifikasi berhasil edit
    console.log('Showing success notification...');
    emit('notification', { message: 'Aplikasi berhasil diperbarui!', type: 'success' });
    emit('saved');
  } catch (e) {
    emit('notification', { message: e.message, type: 'error' });
    console.error('Error:', e);
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
/* Custom scrollbar for the form container */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Penyesuaian desain agar konsisten dengan FormAdd.vue */
.bg-gray-50 {
  background-color: #f9fafb;
}

.bg-white {
  background-color: #fff;
}

.border {
  border-width: 1px;
  border-color: #e5e7eb;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.rounded {
  border-radius: 0.25rem;
}

.p-4 {
  padding: 1rem;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.text-xl {
  font-size: 1.25rem;
  line-height: 1.75rem;
}

.font-bold {
  font-weight: 700;
}

.font-medium {
  font-weight: 500;
}

.text-sm {
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.text-gray-500 {
  color: #6b7280;
}

.text-gray-700 {
  color: #374151;
}

.text-gray-400 {
  color: #9ca3af;
}

.text-blue-600 {
  color: #2563eb;
}

.bg-blue-500 {
  background-color: #3b82f6;
}

.bg-green-500 {
  background-color: #22c55e;
}

.bg-green-600 {
  background-color: #16a34a;
}

.bg-green-700 {
  background-color: #15803d;
}

.bg-purple-500 {
  background-color: #a21caf;
}

.bg-indigo-500 {
  background-color: #6366f1;
}

.bg-orange-500 {
  background-color: #f97316;
}

.text-orange-700 {
  color: #c2410c;
}

/* Infrastructure Styles */
.bg-red-500 {
  background-color: #ef4444;
}

.bg-blue-500 {
  background-color: #3b82f6;
}

.bg-green-500 {
  background-color: #22c55e;
}

.bg-purple-500 {
  background-color: #a21caf;
}

.text-red-600 {
  color: #dc2626;
}

.text-red-700 {
  color: #b91c1c;
}

.text-blue-700 {
  color: #1d4ed8;
}

.text-green-700 {
  color: #15803d;
}

.text-purple-700 {
  color: #7c3aed;
}

.hover\:bg-blue-700:hover {
  background-color: #1d4ed8;
}

.hover\:bg-green-700:hover {
  background-color: #15803d;
}

.hover\:bg-gray-100:hover {
  background-color: #f3f4f6;
}

.hover\:text-gray-700:hover {
  color: #374151;
}

.disabled\:opacity-50:disabled {
  opacity: 0.5;
}

.disabled\:cursor-not-allowed:disabled {
  cursor: not-allowed;
}
</style>