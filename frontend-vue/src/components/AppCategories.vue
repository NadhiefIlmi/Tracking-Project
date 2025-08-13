<template>
  <div class="flex flex-col md:flex-row min-h-screen w-full bg-white text-gray-800">
    <!-- Toast Notification -->
    <div v-if="toast.show" :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2 rounded shadow text-white transition-all', toast.type === 'success' ? 'bg-green-600' : toast.type === 'error' ? 'bg-red-600' : 'bg-yellow-500']">
      <span>{{ toast.message }}</span>
      <template v-if="toast.confirm">
        <button class="ml-4 px-3 py-1 rounded bg-red-700 text-white text-xs" @click="confirmDeleteApp">Ya, Hapus</button>
        <button class="ml-2 px-3 py-1 rounded bg-gray-200 text-gray-700 text-xs" @click="cancelDeleteApp">Batal</button>
      </template>
    </div>

    <!-- Button Switch Tampilan -->
    <div class="fixed top-4 right-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-2 border">
        <div class="flex space-x-1">
          <button 
            @click="setViewMode('graphic')"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium transition-colors',
              viewMode === 'graphic' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            Grafis
          </button>
          <button 
            @click="setViewMode('table')"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium transition-colors',
              viewMode === 'table' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            Tabel
          </button>
        </div>
      </div>
    </div>

    <!-- Tampilan Grafis -->
    <div v-if="viewMode === 'graphic'" class="flex flex-col md:flex-row min-h-screen w-full">
      <!-- Kolom Kiri: Publik & Internal -->
      <div class="flex-1 flex flex-col p-4 md:p-8">
        <!-- Aplikasi Publik -->
        <section class="mb-8">
          <h2 class="text-xl font-bold mb-4 text-center md:text-left">Aplikasi Publik</h2>
          <!-- 
            Atur jumlah kolom di sini:
            grid-cols-3 = 3 kolom per baris (default)
            grid-cols-2 = 2 kolom per baris
            grid-cols-4 = 4 kolom per baris
          -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <AppCard
              v-for="app in apps.filter(a => a.jenis_layanan === 'publik')"
              :key="app.id"
              :title="app.nama_singkat_proses_bisnis"
              :deletable="true"
              @click="showDetail(app)"
              @delete="handleDeleteApp(app)"
              class="cursor-pointer"
            />
          </div>
        </section>
        <!-- Aplikasi Internal -->
        <section>
          <h2 class="text-xl font-bold mb-4 text-center md:text-left">Aplikasi Internal</h2>
          <!-- 
            Atur jumlah kolom di sini:
            grid-cols-3 = 3 kolom per baris (default)
            grid-cols-2 = 2 kolom per baris
            grid-cols-4 = 4 kolom per baris
          -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <AppCard
              v-for="app in apps.filter(a => a.jenis_layanan === 'internal')"
              :key="app.id"
              :title="app.nama_singkat_proses_bisnis"
              :deletable="true"
              @click="showDetail(app)"
              @delete="handleDeleteApp(app)"
              class="cursor-pointer"
            />
          </div>
        </section>
      </div>
      <!-- Garis Vertikal -->
      <div class="hidden md:block w-px bg-gray-300 my-8"></div>
      <!-- Kolom Kanan: Pendukung -->
      <div class="flex-1 flex flex-col p-4 md:p-8">
        <section>
          <h2 class="text-xl font-bold mb-4 text-center md:text-left">Aplikasi Pendukung</h2>
          <!-- 
            Atur jumlah kolom di sini:
            grid-cols-3 = 3 kolom per baris (default)
            grid-cols-2 = 2 kolom per baris
            grid-cols-4 = 4 kolom per baris
          -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <AppCard
              v-for="app in apps.filter(a => a.jenis_layanan === 'pendukung')"
              :key="app.id"
              :title="app.nama_singkat_proses_bisnis"
              :deletable="true"
              @click="showDetail(app)"
              @delete="handleDeleteApp(app)"
              class="cursor-pointer"
            />
          </div>
        </section>
      </div>
    </div>

    <!-- Tampilan Tabel -->
    <div v-else-if="viewMode === 'table'" class="w-full">
      <Tabular :refreshKey="refreshKey" @appDeleted="handleAppDeleted" />
    </div>
  
    <!-- Tombol Tambah Mengambang -->
    <button
      class="fixed bottom-8 right-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full shadow-lg z-50"
      @click="showAddForm = true"
    >
      Tambah
    </button>

    <!-- Modal Form Tambah -->
    <FormAdd v-if="showAddForm" @close="showAddForm = false" @saved="handleAppSaved" @notification="handleNotification" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppCard from './AppCard.vue'
import FormAdd from './FormAdd.vue'
import Tabular from './Tabular.vue'

const apps = ref([])
const selectedApp = ref(null)
const router = useRouter()
const route = useRoute()
const showAddForm = ref(false)
const viewMode = ref(route.query.mode || 'graphic') // 'graphic' atau 'table'
const refreshKey = ref(0)

// Toast state
const toast = ref({ show: false, message: '', type: 'info', confirm: false })
const appIdToDelete = ref(null)

async function fetchApps() {
  const res = await fetch('http://localhost:8000/api/aplikasi')
  apps.value = await res.json()
}

onMounted(fetchApps)

function showDetail(app) {
  router.push({ name: 'AppDetail', params: { id: app.id } })
}

function handleNotification(notif) {
  toast.value = {
    show: true,
    message: notif.message,
    type: notif.type,
    confirm: false
  }
  setTimeout(() => { toast.value.show = false }, 2500)
}

function handleAppSaved() {
  showAddForm.value = false
  fetchApps()
  refreshKey.value++ // Trigger refresh di Tabular component
}

function handleAppDeleted() {
  fetchApps()
  refreshKey.value++ // Trigger refresh di Tabular component
}

function handleDeleteApp(app) {
  toast.value = {
    show: true,
    message: `Apakah anda yakin ingin menghapus aplikasi "${app.nama_singkat_proses_bisnis}"?`,
    type: 'warning',
    confirm: true
  }
  appIdToDelete.value = app.id
}

async function confirmDeleteApp() {
  if (!appIdToDelete.value) return
  try {
    const res = await fetch(`http://localhost:8000/api/aplikasi/${appIdToDelete.value}`, { method: 'DELETE' })
    if (!res.ok) throw new Error(await res.text())
    toast.value = { show: true, message: 'Aplikasi berhasil dihapus', type: 'success', confirm: false }
    fetchApps()
    refreshKey.value++ // Trigger refresh di Tabular component
  } catch (e) {
    toast.value = { show: true, message: 'Gagal menghapus aplikasi', type: 'error', confirm: false }
  } finally {
    appIdToDelete.value = null
    setTimeout(() => { toast.value.show = false }, 2500)
  }
}

function cancelDeleteApp() {
  toast.value = { show: false, message: '', type: 'info', confirm: false }
  appIdToDelete.value = null
}

// Fungsi untuk mengatur view mode dan mengupdate URL
function setViewMode(mode) {
  viewMode.value = mode
  router.push({ query: { ...route.query, mode } })
}

// Watch untuk perubahan query parameter
watch(() => route.query.mode, (newMode) => {
  if (newMode && newMode !== viewMode.value) {
    viewMode.value = newMode
  }
})
</script>