import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import CatalogView from '../views/CatalogView.vue'
import WorkDetailView from '../views/WorkDetailView.vue'
import PurchasesView from '../views/PurchasesView.vue'
import ProfileView from '../views/ProfileView.vue'
import ReferralView from '../views/ReferralView.vue'
import AdminView from '../views/AdminView.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/catalog', name: 'catalog', component: CatalogView },
  { path: '/work/:id', name: 'work-detail', component: WorkDetailView },
  { path: '/purchases', name: 'purchases', component: PurchasesView },
  { path: '/referral', name: 'referral', component: ReferralView },
  { path: '/profile', name: 'profile', component: ProfileView },
  { path: '/admin', name: 'admin', component: AdminView },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

export default router
