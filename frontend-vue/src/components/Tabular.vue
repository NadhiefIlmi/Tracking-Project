<template>
  <!-- Toast Notification -->
  <div v-if="toast.show" :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2 rounded shadow text-white transition-all', toast.type === 'success' ? 'bg-green-600' : toast.type === 'error' ? 'bg-red-600' : 'bg-yellow-500']">
    <span>{{ toast.message }}</span>
    <template v-if="toast.confirm">
      <button class="ml-4 px-3 py-1 rounded bg-red-700 text-white text-xs" @click="confirmDeleteApp">Ya, Hapus</button>
      <button class="ml-2 px-3 py-1 rounded bg-gray-200 text-gray-700 text-xs" @click="cancelDeleteApp">Batal</button>
    </template>
  </div>

  <div class="h-screen bg-white p-8 overflow-hidden">
    <div class="max-w-7xl mx-auto h-full flex flex-col">
      <!-- Header -->
      <div class="text-center mb-4 flex-shrink-0">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Daftar Aplikasi</h1>
      </div>

      <!-- Search and Filter -->
      <div class="mb-4 flex flex-col sm:flex-row gap-4 flex-shrink-0">
        <div class="flex-1">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari aplikasi atau project..." 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
        <div class="flex gap-2">
          <select 
            v-model="filterType" 
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Semua Jenis</option>
            <option value="publik">Publik</option>
            <option value="internal">Internal</option>
            <option value="pendukung">Pendukung</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden flex-1 flex flex-col">
        <div class="overflow-y-auto flex-1 table-container">
          <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
              <tr>
                <th class="w-12 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  No
                </th>
                <th class="w-48 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Nama Aplikasi
                </th>
                <th class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Jenis Layanan
                </th>
                <th class="w-40 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Nama Project
                </th>
                <th class="w-48 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Framework
                </th>
                <th class="w-64 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Deployment
                </th>
                <th class="w-40 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Dependency Package
                </th>
                <th class="w-48 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Service API Gate
                </th>
                <th class="w-48 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                  Route API Gate
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <template v-for="(app, appIndex) in filteredGroupedApps" :key="app.appId">
                <template v-if="app.projects.length > 0">
                  <template v-for="(project, projectIndex) in app.projects" :key="project.projectId">
                    <!-- Deployment Rows -->
                    <template v-if="project.deployments.length > 0">
                      <tr v-for="(deployment, depIndex) in project.deployments" 
                          :key="`dep-${deployment.id || depIndex}`"
                          :class="{
                            'hover:bg-gray-50 transition-colors duration-200': true,
                            'border-t border-gray-200': projectIndex > 0 || depIndex > 0
                          }">
                        <!-- App Info (first row only) -->
                        <td v-if="projectIndex === 0 && depIndex === 0" 
                            :rowspan="getAppRowspan(app)"
                            class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                          {{ appIndex + 1 }}
                        </td>
                        <td v-if="projectIndex === 0 && depIndex === 0" 
                            :rowspan="getAppRowspan(app)"
                            class="px-3 py-3 text-xs text-gray-900 border-r border-gray-300 app-name-cell">
                          <div class="relative">
                            <span class="font-medium hover:text-blue-600 transition-colors duration-200 cursor-pointer pr-8 break-words block whitespace-normal"
                                  @click="navigateToAppDetail(app.appId)">
                              {{ app.appName }}
                            </span>
                            <button 
                              @click.stop="handleDeleteApp(app)"
                              class="text-red-500 hover:text-red-700 transition-colors duration-200 absolute top-0 right-0"
                              title="Hapus aplikasi"
                            >
                              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                              </svg>
                            </button>
                          </div>
                        </td>
                        <td v-if="projectIndex === 0 && depIndex === 0" 
                            :rowspan="getAppRowspan(app)"
                            class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                          <span :class="[
                            'px-2 py-1 text-xs font-medium rounded-full',
                            app.jenisLayanan === 'publik' ? 'bg-green-100 text-green-800' :
                            app.jenisLayanan === 'internal' ? 'bg-blue-100 text-blue-800' :
                            'bg-yellow-100 text-yellow-800'
                          ]">
                            {{ app.jenisLayanan }}
                          </span>
                        </td>
                        
                        <!-- Project Info (first deployment row only) -->
                        <td v-if="depIndex === 0" 
                            :rowspan="project.deployments.length"
                            class="px-3 py-3 text-xs text-gray-900 border-r border-gray-300 project-name-cell">
                          <span class="break-words whitespace-normal">{{ project.projectName || '-' }}</span>
                        </td>
                        
                        <!-- Framework Info (first deployment row only) -->
                        <td v-if="depIndex === 0" 
                            :rowspan="project.deployments.length"
                            class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                          <span v-if="project.technology || project.framework || project.version">
                            {{ project.technology || '-' }} ({{ project.framework || '-' }} v{{ project.version || '-' }})
                          </span>
                          <span v-else>-</span>
                        </td>
                        
                        <!-- Deployment Info -->
                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                          {{ deployment.service_name || deployment.namespace || 'Unnamed Deployment' }}
                          <span v-if="deployment.tahap" class="text-gray-500">({{ deployment.tahap }})</span>
                        </td>
                        
                        <!-- Dependency Packages -->
                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                          <div v-if="deployment.dependency_packages && deployment.dependency_packages.length > 0">
                            <div v-for="(pkg, pkgIndex) in deployment.dependency_packages" 
                                 :key="`pkg-${pkg.id || pkgIndex}`"
                                 :class="{'mt-1': pkgIndex > 0}">
                              {{ pkg.dependency_name || 'Unnamed Package' }} v{{ pkg.version || '-' }}
                            </div>
                          </div>
                          <div v-else>-</div>
                        </td>
                        
                        <!-- Service API Gates -->
                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                          <div v-if="deployment.service_api_gates && deployment.service_api_gates.length > 0">
                            <div v-for="(service, svcIndex) in deployment.service_api_gates" 
                                 :key="`svc-${service.id || svcIndex}`"
                                 :class="{'mt-1': svcIndex > 0}">
                              {{ service.api_gate || '-' }}  {{ service.service_name || '-' }} : {{ service.path || '-' }}
                            </div>
                          </div>
                          <div v-else>-</div>
                        </td>
                        
                        <!-- Route API Gates -->
                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900">
                          <div v-if="deployment.service_api_gates && deployment.service_api_gates.length > 0">
                            <template v-for="(service, svcIndex) in deployment.service_api_gates">
                              <div v-if="service.route_api_gates && service.route_api_gates.length > 0">
                                <div v-for="(route, routeIndex) in service.route_api_gates" 
                                     :key="`route-${route.id || routeIndex}`"
                                     :class="{'mt-1': routeIndex > 0 || svcIndex > 0}">
                                  {{ route.route_name || '-' }} : {{ route.path || '-' }} {{ route.protocol || '-' }}
                                </div>
                              </div>
                              <div v-else>-</div>
                            </template>
                          </div>
                          <div v-else>-</div>
                        </td>
                      </tr>
                    </template>
                    
                    <!-- Empty Deployment Row -->
                    <tr v-else
                        :class="{'hover:bg-gray-50 transition-colors duration-200': true, 'border-t border-gray-200': projectIndex > 0}">
                      <td v-if="projectIndex === 0" 
                          :rowspan="getAppRowspan(app)"
                          class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                        {{ appIndex + 1 }}
                      </td>
                      <td v-if="projectIndex === 0" 
                          :rowspan="getAppRowspan(app)"
                          class="px-3 py-3 text-xs text-gray-900 border-r border-gray-300 app-name-cell">
                        <div class="relative">
                          <span class="font-medium hover:text-blue-600 transition-colors duration-200 cursor-pointer pr-8 break-words block whitespace-normal"
                                @click="navigateToAppDetail(app.appId)">
                            {{ app.appName }}
                          </span>
                          <button 
                            @click.stop="handleDeleteApp(app)"
                            class="text-red-500 hover:text-red-700 transition-colors duration-200 absolute top-0 right-0"
                            title="Hapus aplikasi"
                          >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                          </button>
                        </div>
                      </td>
                      <td v-if="projectIndex === 0" 
                          :rowspan="getAppRowspan(app)"
                          class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                        <span :class="[
                          'px-2 py-1 text-xs font-medium rounded-full',
                          app.jenisLayanan === 'publik' ? 'bg-green-100 text-green-800' :
                          app.jenisLayanan === 'internal' ? 'bg-blue-100 text-blue-800' :
                          'bg-yellow-100 text-yellow-800'
                        ]">
                          {{ app.jenisLayanan }}
                        </span>
                      </td>
                      
                      <td class="px-3 py-3 text-xs text-gray-900 border-r border-gray-300 project-name-cell">
                        <span class="break-words whitespace-normal">{{ project.projectName || '-' }}</span>
                      </td>
                      <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                        <span v-if="project.technology || project.framework || project.version">
                          {{ project.technology || '-' }} ({{ project.framework || '-' }} v{{ project.version || '-' }})
                        </span>
                        <span v-else>-</span>
                      </td>
                      <td colspan="3" class="px-3 py-3 whitespace-nowrap text-xs text-gray-500 italic">
                        Tidak ada deployment
                      </td>
                    </tr>
                  </template>
                </template>
                
                <!-- Empty Project Row -->
                <tr v-else 
                    class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                    {{ appIndex + 1 }}
                  </td>
                  <td class="px-3 py-3 text-xs text-gray-900 border-r border-gray-300 app-name-cell">
                    <div class="relative">
                      <span class="font-medium hover:text-blue-600 transition-colors duration-200 cursor-pointer pr-8 break-words block whitespace-normal"
                            @click="navigateToAppDetail(app.appId)">
                        {{ app.appName }}
                      </span>
                      <button 
                        @click.stop="handleDeleteApp(app)"
                        class="text-red-500 hover:text-red-700 transition-colors duration-200 absolute top-0 right-0"
                        title="Hapus aplikasi"
                      >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                  <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300">
                    <span :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      app.jenisLayanan === 'publik' ? 'bg-green-100 text-green-800' :
                      app.jenisLayanan === 'internal' ? 'bg-blue-100 text-blue-800' :
                      'bg-yellow-100 text-yellow-800'
                    ]">
                      {{ app.jenisLayanan }}
                    </span>
                  </td>
                  <td colspan="6" class="px-3 py-3 whitespace-nowrap text-xs text-gray-500 italic">
                    Tidak ada project
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="filteredGroupedApps.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
          <p class="mt-1 text-sm text-gray-500">Tidak ada project yang ditemukan dengan kriteria pencarian ini.</p>
        </div>
      </div>

      <!-- Summary -->
      <div class="bg-gray-50 rounded-lg p-4 flex-shrink-0 mt-4">
        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
          <div>Total: <span class="font-medium">{{ filteredGroupedApps.length }}</span> aplikasi</div>
          <div>Publik: <span class="font-medium">{{ getCountByType('publik') }}</span></div>
          <div>Internal: <span class="font-medium">{{ getCountByType('internal') }}</span></div>
          <div>Pendukung: <span class="font-medium">{{ getCountByType('pendukung') }}</span></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

// Props untuk mengontrol refresh data
const props = defineProps({
  refreshKey: {
    type: Number,
    default: 0
  }
})

// Emit events untuk komunikasi dengan parent
const emit = defineEmits(['appDeleted'])

const router = useRouter()
const apps = ref([])
const searchQuery = ref('')
const filterType = ref('')

// Toast state untuk notifikasi
const toast = ref({ show: false, message: '', type: 'info', confirm: false })
const appIdToDelete = ref(null)

// Fungsi untuk navigasi ke halaman detail aplikasi
const navigateToAppDetail = (appId) => {
  router.push(`/apps/${appId}`)
}

// Fetch data aplikasi
async function fetchApps() {
  try {
    const response = await fetch('http://localhost:8000/api/aplikasi')
    const data = await response.json()
    apps.value = data
  } catch (error) {
    console.error('Error fetching apps:', error)
  }
}

// Watch untuk refreshKey prop
watch(() => props.refreshKey, () => {
  fetchApps()
})

// Fungsi untuk handle delete app
function handleDeleteApp(app) {
  toast.value = {
    show: true,
    message: `Apakah anda yakin ingin menghapus aplikasi "${app.appName}"?`,
    type: 'warning',
    confirm: true
  }
  appIdToDelete.value = app.appId
}

// Fungsi untuk confirm delete app
async function confirmDeleteApp() {
  if (!appIdToDelete.value) return
  try {
    const res = await fetch(`http://localhost:8000/api/aplikasi/${appIdToDelete.value}`, { method: 'DELETE' })
    if (!res.ok) throw new Error(await res.text())
    toast.value = { show: true, message: 'Aplikasi berhasil dihapus', type: 'success', confirm: false }
    fetchApps()
    // Emit event ke parent untuk update data di AppCategories
    emit('appDeleted')
  } catch (e) {
    toast.value = { show: true, message: 'Gagal menghapus aplikasi', type: 'error', confirm: false }
  } finally {
    appIdToDelete.value = null
    setTimeout(() => { toast.value.show = false }, 2500)
  }
}

// Fungsi untuk cancel delete
function cancelDeleteApp() {
  toast.value = { show: false, message: '', type: 'info', confirm: false }
  appIdToDelete.value = null
}

// Transform data aplikasi menjadi grouped apps with projects and deployments
const groupedApps = computed(() => {
  return apps.value.map(app => {
    const projects = app.projects && app.projects.length > 0 ? app.projects.map(project => {
      // Pastikan deployments ada atau array kosong
      const deployments = project.deployments || []
      
      // Transform each deployment
      const transformedDeployments = deployments.map(deployment => {
        // Pastikan semua nested array ada
        return {
          id: deployment.id,
          service_name: deployment.service_name,
          namespace: deployment.namespace,
          tahap: deployment.tahap,
          dependency_packages: deployment.dependency_packages || [],
          service_api_gates: deployment.service_api_gates || []
        }
      })
      
      return {
        projectId: project.id,
        projectName: project.project_name,
        technology: project.technology,
        framework: project.framework,
        version: project.version,
        deployments: transformedDeployments
      }
    }) : []
    
    return {
      appId: app.id,
      appName: app.nama_singkat_proses_bisnis,
      jenisLayanan: app.jenis_layanan,
      projects: projects
    }
  })
})

// Hitung total rowspan untuk aplikasi
function getAppRowspan(app) {
  if (app.projects.length === 0) return 1
  return app.projects.reduce((total, project) => {
    return total + (project.deployments.length > 0 ? project.deployments.length : 1)
  }, 0)
}

// Filter grouped apps based on search and filter
const filteredGroupedApps = computed(() => {
  let filtered = groupedApps.value

  if (filterType.value) {
    filtered = filtered.filter(app => app.jenisLayanan === filterType.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(app => {
      // Cek nama aplikasi
      if (app.appName.toLowerCase().includes(query)) return true
      
      // Cek semua project dan deployment
      return app.projects.some(project => {
        // Cek nama project
        if (project.projectName.toLowerCase().includes(query)) return true
        
        // Cek semua deployment
        return project.deployments.some(deployment => {
          // Cek nama deployment
          if ((deployment.service_name || '').toLowerCase().includes(query) || 
              (deployment.namespace  || '').toLowerCase().includes(query)) return true
          
          // Cek dependency packages
          if (deployment.dependency_packages.some(pkg => 
            (pkg.dependency_name || '').toLowerCase().includes(query))) return true
          
          // Cek service api gates
          if (deployment.service_api_gates.some(service => 
            (service.service_name || '').toLowerCase().includes(query) ||
            (service.api_gate || '').toLowerCase().includes(query))) return true
          
          // Cek route api gates
          return deployment.service_api_gates.some(service => 
            service.route_api_gates && service.route_api_gates.some(route => 
              (route.route_name || '').toLowerCase().includes(query) ||
              (route.path || '').toLowerCase().includes(query)
            )
          )
        })
      })
    })
  }

  return filtered
})

// Hitung jumlah berdasarkan jenis layanan
function getCountByType(type) {
  return filteredGroupedApps.value.filter(item => item.jenisLayanan === type).length
}

onMounted(fetchApps)
</script>

<style scoped>
/* Custom styles untuk tabel */
table {
  border-collapse: collapse;
  border: 1px solid #d1d5db;
}

th, td {
  border-right: 1px solid #d1d5db;
  border-bottom: 1px solid #d1d5db;
}

th:last-child, td:last-child {
  border-right: none;
}

/* Style untuk rowspan borders */
tr {
  border-bottom: 1px solid #d1d5db;
}

/* Style untuk table container border */
.table-container {
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
}

/* Style untuk nested items */
.mt-1 {
  margin-top: 0.25rem;
}

/* Style untuk sticky header */
thead.sticky {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Style untuk text truncation */
.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Style untuk compact table */
td {
  max-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Style untuk app name text wrapping */
.app-name-cell {
  white-space: normal !important;
  word-wrap: break-word !important;
  word-break: break-word !important;
  overflow-wrap: break-word !important;
}

/* Style untuk project name text wrapping */
.project-name-cell {
  white-space: normal !important;
  word-wrap: break-word !important;
  word-break: break-word !important;
  overflow-wrap: break-word !important;
}

/* Style untuk scroll container */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f7fafc;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
</style>