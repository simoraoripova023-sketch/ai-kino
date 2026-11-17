import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import CatalogView from '../views/CatalogView.vue'
import WorkDetailView from '../views/WorkDetailView.vue'
import PurchasesView from '../views/PurchasesView.vue'
import ProfileView from '../views/ProfileView.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/catalog', name: 'catalog', component: CatalogView },
  { path: '/work/:id', name: 'work-detail', component: WorkDetailView },
  { path: '/purchases', name: 'purchases', component: PurchasesView },
  { path: '/profile', name: 'profile', component: ProfileView },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

export default router
