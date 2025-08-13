import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router'
import AppCategories from './components/AppCategories.vue'
import AppDetail from './components/AppDetail.vue'

const routes = [
  { path: '/', component: AppCategories },
  { path: '/apps/:id', component: AppDetail, name: 'AppDetail' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

createApp(App).use(router).mount('#app')
