<template>
  <div
    class="relative bg-white border border-gray-200 rounded-xl shadow-sm flex items-center justify-center w-full h-32 p-4 transition hover:shadow-md"
    :class="{
      'border-dashed border-2 border-gray-300 bg-gray-50 text-gray-400': !title
    }"
  >
    <span v-if="label" class="absolute top-2 right-2 bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded">{{ label }}</span>
    <button v-if="deletable" @click.stop="$emit('delete')" class="absolute top-2 right-2 z-10 text-gray-400 hover:text-red-500 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
    <span v-if="title" class="font-semibold text-base">{{ title }}</span>
    <span v-else class="italic">Belum dimuat</span>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  label: String,
  deletable: Boolean
})
</script> 