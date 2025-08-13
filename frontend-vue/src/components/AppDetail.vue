<template>
  <div class="px-4 py-8 relative w-full">
    <!-- Back button -->
    <button
      @click="$router.go(-1)"
      class="mb-6 flex items-center text-gray-600 hover:text-gray-800"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 mr-1"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
          clip-rule="evenodd"
        />
      </svg>
      Kembali ke Daftar Aplikasi
    </button>
    <!-- Diagram Hierarki D3.js (komponen terpisah) -->
    <AppHierarchyD3 :app="app" @node-click="handleD3NodeClick" class="mb-10" />
    
    <!-- Detail Sheet Overlay -->
    <div 
      v-if="showDetailSheet"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity duration-300"
      @click="closeDetailSheet"
    ></div>

    <!-- Detail Sheet Content -->
    <div 
      class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-lg z-50 transform transition-transform duration-300 ease-in-out"
      :class="{'translate-x-0': showDetailSheet, 'translate-x-full': !showDetailSheet}"
    >
      <div class="h-full flex flex-col">
        <!-- Sheet Header -->
        <div class="flex justify-between items-center border-b border-gray-200 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-800">
            {{ detailSheetTitle }}
          </h3>
          <button @click="closeDetailSheet" class="text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Sheet Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <div class="space-y-4">
            <!-- App Details -->
            <div v-if="currentDetail.type === 'app'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Singkat Proses Bisnis</h4>
              <p class="mt-1 text-sm text-gray-800">{{ app.nama_singkat_proses_bisnis }}</p>
            </div>

            <div v-if="currentDetail.type === 'app'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aplikasi</h4>
              <p class="mt-1 text-sm text-gray-800">{{ app.nama_aplikasi }}</p>
            </div>
            
            <div v-if="currentDetail.type === 'app'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Proses Bisnis</h4>
              <p class="mt-1 text-sm text-gray-800">{{ app.proses_bisnis }}</p>
            </div>
            
            <div v-if="currentDetail.type === 'app'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja Pemilik</h4>
              <p class="mt-1 text-sm text-gray-800">{{ app.unit_kerja_pemilik_proses_bisnis }}</p>
            </div>
            
            <div v-if="currentDetail.type === 'app'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Penanggung Jawab</h4>
              <p class="mt-1 text-sm text-gray-800">{{ app.penanggung_jawab_aplikasi }}</p>
            </div>

            <!-- Project Details -->
            <div v-if="currentDetail.type === 'project'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.project_name || '-' }}</p>
            </div>
            
            <div v-if="currentDetail.type === 'project'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Base Project</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.base_project || '-' }}</p>
            </div>
            
            <div v-if="currentDetail.type === 'project'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Fungsi</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.fungsi || '-' }}</p>
            </div>
            
            <div v-if="currentDetail.type === 'project'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Framework</h4>
              <p class="mt-1 text-sm text-gray-800">
                {{ (currentDetail.data.technology || '-') }} ({{ currentDetail.data.framework }} v{{ currentDetail.data.version}})
              </p>
            </div>



            <!-- Deployment Details -->
            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Namespace</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.namespace || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Type</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.type || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Service Name</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.service_name || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Cluster IP</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.cluster_ip || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">External IP</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.external_ip || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Port</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.port || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Environment</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.env || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis App</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.jenis_app || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tahap</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.tahap || '-' }}</p>
            </div>

            <div v-if="currentDetail.type === 'deployment' && currentDetail.data.dependency_packages && currentDetail.data.dependency_packages.length">
              <h4 class="text-xs font-medium text-gray-900 uppercase tracking-wider mt-6">Detail Dependency Package</h4>
              <div class="mt-2 space-y-4">
                <div v-for="dependency in currentDetail.data.dependency_packages" :key="dependency.id || dependency.dependency_name" class="border-l-2 border-gray-300 pl-3 py-2 space-y-2">
                  <div>
                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Dependency Name</h4>
                    <p class="mt-1 text-sm text-gray-800">{{ dependency.dependency_name || '-' }}</p>
                  </div>
                  <div>
                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Version</h4>
                    <p class="mt-1 text-sm text-gray-800">{{ dependency.version || '-' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Service Details (tampilkan jika type service) -->
            <div v-if="currentDetail.type === 'service'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Api Gate</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.api_gate || '-' }}</p>
            </div>
            <div v-if="currentDetail.type === 'service'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Service Name</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.service_name || '-' }}</p>
            </div>
            <div v-if="currentDetail.type === 'service'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Protocol</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.protocol || '-' }}</p>
            </div>
            <div v-if="currentDetail.type === 'service'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Path</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.path || '-' }}</p>
            </div>
            <div v-if="currentDetail.type === 'service' && ((currentDetail.data.route_api_gates && currentDetail.data.route_api_gates.length) || (currentDetail.data.routeApiGates && currentDetail.data.routeApiGates.length))">
              <h4 class="text-xs font-medium text-gray-900 uppercase tracking-wider mt-6">Detail Route API Gate</h4>
              <div class="mt-2 space-y-4">
                <div v-for="route in (currentDetail.data.route_api_gates || currentDetail.data.routeApiGates)" :key="route.id || route.route_name" class="border-l-2 border-gray-300 pl-3 py-2 space-y-2">
                  <div>
                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Route Name</h4>
                    <p class="mt-1 text-sm text-gray-800">{{ route.route_name || '-' }}</p>
                  </div>
                  <div>
                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Path</h4>
                    <p class="mt-1 text-sm text-gray-800">{{ route.path || '-' }}</p>
                  </div>
                  <div>
                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Protocol</h4>
                    <p class="mt-1 text-sm text-gray-800">{{ route.protocol || '-' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- WAF Details -->
            <div v-if="currentDetail.type === 'waf'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">IP WAF</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.ip_waf || '-' }}</p>
            </div>

            <!-- IP Publik Details -->
            <div v-if="currentDetail.type === 'ipPublik'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">IP Publik</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.ip_publik || '-' }}</p>
            </div>

            <!-- Inperva Details -->
            <div v-if="currentDetail.type === 'inperva'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.name || '-' }}</p>
            </div>

            <!-- DNS Details -->
            <div v-if="currentDetail.type === 'dns'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.name || '-' }}</p>
            </div>
            
            <div v-if="currentDetail.type === 'dns'">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Type</h4>
              <p class="mt-1 text-sm text-gray-800">{{ currentDetail.data.type || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Sheet Footer -->
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-gray-300"></div>
    </div>
  </div>
  <!-- Notification Toast -->
  <div v-if="notification.show" 
       :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2 rounded shadow text-white transition-all', notification.type === 'success' ? 'bg-green-600' : 'bg-red-600']">
    <span>{{ notification.message }}</span>
  </div>

  <FormEdit v-if="showEditForm" :app="app" @close="closeEditForm" @saved="handleEditSaved" @notification="handleNotification" />
  <button
    class="fixed bottom-8 right-8 z-50 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg px-6 py-3 flex items-center gap-2 transition-all duration-200"
    @click="openEditForm"
  >
    Edit
  </button>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRoute } from "vue-router";
import AppHierarchyD3 from './AppHierarchyD3.vue';
import FormEdit from './FormEdit.vue';

const route = useRoute();
const app = ref({});
const loading = ref(true);
const expandedProjects = ref([]);
const expandedDeployments = ref({});
const expandedApiServices = ref({});
const showApiRoutes = ref(true);

// Detail sheet state
const showDetailSheet = ref(false);
const currentDetail = ref({
  type: null, // 'app', 'project', or 'deployment'
  data: null
});

const detailSheetTitle = computed(() => {
  if (currentDetail.value.type === 'app') {
    return 'Detail Aplikasi';
  } else if (currentDetail.value.type === 'project') {
    return 'Detail Proyek';
  } else if (currentDetail.value.type === 'deployment') {
    return 'Detail Deployment';
  } else if (currentDetail.value.type === 'service') {
    return 'Detail Service API Gates';
  } else if (currentDetail.value.type === 'waf') {
    return 'Detail WAF';
  } else if (currentDetail.value.type === 'ipPublik') {
    return 'Detail IP Publik';
  } else if (currentDetail.value.type === 'inperva') {
    return 'Detail Inperva';
  } else if (currentDetail.value.type === 'dns') {
    return 'Detail DNS';
  }
  return 'Detail';
});

const openDetailSheet = (type, data = null) => {
  currentDetail.value = { type, data };
  showDetailSheet.value = true;
  document.body.style.overflow = 'hidden';
};

const closeDetailSheet = () => {
  showDetailSheet.value = false;
  document.body.style.overflow = '';
  setTimeout(() => {
    currentDetail.value = { type: null, data: null };
  }, 300);
};

const toggleProject = (index) => {
  const currentIndex = expandedProjects.value.indexOf(index);
  if (currentIndex === -1) {
    expandedProjects.value.push(index);
  } else {
    expandedProjects.value.splice(currentIndex, 1);
  }
};

const toggleDeployment = (pIndex, dIndex) => {
  const key = `${pIndex}-${dIndex}`;
  expandedDeployments.value[key] = !expandedDeployments.value[key];
};

const toggleApiService = (pIndex, dIndex, aIndex) => {
  const key = `${pIndex}-${dIndex}-${aIndex}`;
  expandedApiServices.value[key] = !expandedApiServices.value[key];
};

const toggleApiRoutes = () => {
  showApiRoutes.value = !showApiRoutes.value;
};

const handleD3NodeClick = (node) => {
  if (node.type === 'app') {
    openDetailSheet('app', app.value);
  } else if (node.type === 'project') {
    openDetailSheet('project', node.data);
  } else if (node.type === 'deployment') {
    openDetailSheet('deployment', node.data);
  } else if (node.type === 'service') {
    openDetailSheet('service', node.data);
  }
};

const showEditForm = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });

const openEditForm = () => { showEditForm.value = true; };
const closeEditForm = () => { showEditForm.value = false; };

function handleNotification(notif) {
  notification.value = { show: true, message: notif.message, type: notif.type };
  setTimeout(() => {
    notification.value.show = false;
  }, 3000);
}

function handleEditSaved() {
  closeEditForm();
  fetchAppDetail();
}

async function fetchAppDetail() {
  loading.value = true;
  try {
    const response = await fetch(
      `http://localhost:8000/api/apps/${route.params.id}/detail`
    );
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    app.value = await response.json();
  } catch (error) {
    console.error("Error fetching app details:", error);
  } finally {
    loading.value = false;
  }
}

onMounted(fetchAppDetail);
</script>

<style scoped>
.container {
  max-width: 1200px;
}

/* Smooth transitions for the detail sheet */
.transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Prevent scrolling when detail sheet is open */
body {
  overflow-x: hidden;
}

/* Custom scrollbar for sheet content */
.flex-1::-webkit-scrollbar {
  width: 6px;
}
.flex-1::-webkit-scrollbar-track {
  background: #f1f1f1;
}
.flex-1::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}
.flex-1::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.diagram-node {
  min-width: 120px;
  min-height: 48px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px 0 rgba(0,0,0,0.04);
  border: 1.5px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: box-shadow 0.2s, border 0.2s;
}
.diagram-node:hover {
  box-shadow: 0 4px 16px 0 rgba(59,130,246,0.10);
  border-color: #60a5fa;
}
.diagram-node.app {
  background: #e0f2fe;
}
.diagram-node.project {
  background: #dcfce7;
}
.diagram-node.deployment {
  background: #ede9fe;
}
.diagram-node.service {
  background: #fce7f3;
}

.w-full {
  width: 100%;
}
.overflow-x-auto {
  overflow-x: auto;
}
</style>