<template>
  <div class="space-y-5 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <router-link to="/profile" class="p-2 rounded-xl bg-cinema-900 border border-white/5 text-gray-400 hover:text-white transition">
          &larr;
        </router-link>
        <div>
          <h1 class="text-base font-black text-white flex items-center gap-1.5">
            <span>👑</span> {{ t('admin_panel_title') }}
          </h1>
          <p class="text-[10px] text-gray-400">{{ t('admin_panel_sub') }}</p>
        </div>
      </div>
      <button 
        @click="loadAll" 
        :disabled="isLoading"
        class="px-2.5 py-1 rounded-lg bg-cinema-800 text-[11px] font-bold text-cinema-gold hover:bg-cinema-700 transition"
      >
        {{ isLoading ? '...' : '↻ ' + t('btn_refresh') }}
      </button>
    </div>

    <!-- Overview Stats Cards -->
    <div v-if="overview" class="grid grid-cols-2 gap-2.5">
      <div class="p-3 rounded-xl bg-cinema-900 border border-white/5 space-y-1">
        <div class="text-[10px] text-gray-400 uppercase font-bold">{{ t('stat_total_users') }}</div>
        <div class="text-xl font-black text-white">{{ overview.users?.total || 0 }}</div>
        <div class="text-[10px] text-emerald-400">DAU: {{ overview.users?.dau || 0 }} • MAU: {{ overview.users?.mau || 0 }}</div>
      </div>
      <div class="p-3 rounded-xl bg-cinema-900 border border-white/5 space-y-1">
        <div class="text-[10px] text-gray-400 uppercase font-bold">{{ t('stat_revenue_stars') }}</div>
        <div class="text-xl font-black text-amber-300">⭐ {{ overview.revenue?.net_stars || 0 }}</div>
        <div class="text-[10px] text-gray-400">{{ overview.revenue?.total_transactions || 0 }} {{ t('transactions') }}</div>
      </div>
      <div class="p-3 rounded-xl bg-cinema-900 border border-white/5 space-y-1">
        <div class="text-[10px] text-gray-400 uppercase font-bold">{{ t('stat_works') }}</div>
        <div class="text-xl font-black text-white">{{ overview.catalog?.published_works || 0 }} <span class="text-xs text-gray-400">/ {{ overview.catalog?.total_works || 0 }}</span></div>
        <div class="text-[10px] text-emerald-400">{{ overview.catalog?.published_episodes || 0 }} {{ t('episodes') }}</div>
      </div>
      <div class="p-3 rounded-xl bg-cinema-900 border border-white/5 space-y-1">
        <div class="text-[10px] text-gray-400 uppercase font-bold">{{ t('stat_delivered') }}</div>
        <div class="text-xl font-black text-cinema-gold">{{ overview.playback?.delivered_videos || 0 }}</div>
        <div class="text-[10px] text-gray-400">Telegram Bot copyMessage</div>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex gap-2 border-b border-white/10 pb-2">
      <button 
        @click="activeTab = 'works'"
        class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition"
        :class="activeTab === 'works' ? 'bg-cinema-gold text-black' : 'text-gray-400 hover:text-white bg-cinema-900'"
      >
        🎬 {{ t('tab_works') }}
      </button>
      <button 
        @click="activeTab = 'payments'"
        class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition"
        :class="activeTab === 'payments' ? 'bg-cinema-gold text-black' : 'text-gray-400 hover:text-white bg-cinema-900'"
      >
        ⭐ {{ t('tab_payments') }}
      </button>
      <button 
        @click="activeTab = 'audit'"
        class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition"
        :class="activeTab === 'audit' ? 'bg-cinema-gold text-black' : 'text-gray-400 hover:text-white bg-cinema-900'"
      >
        📋 {{ t('tab_audit') }}
      </button>
    </div>

    <!-- Alert / Toast -->
    <div v-if="alertMessage" class="p-3 rounded-xl bg-cinema-800 border border-cinema-gold/40 text-xs font-semibold text-cinema-gold flex items-center justify-between">
      <span>{{ alertMessage }}</span>
      <button @click="alertMessage = ''" class="text-white">&times;</button>
    </div>

    <!-- TAB 1: WORKS MANAGEMENT -->
    <div v-if="activeTab === 'works'" class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-extrabold text-white uppercase">{{ t('all_works_list') }} ({{ works.length }})</h3>
        <button 
          @click="showCreateModal = true"
          class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-cinema-gold to-amber-500 text-black font-extrabold text-xs shadow-md active:scale-95 transition"
        >
          + {{ t('btn_add_work') }}
        </button>
      </div>

      <div class="space-y-2">
        <div 
          v-for="work in works" 
          :key="work.id"
          class="p-3 rounded-xl bg-cinema-900 border border-white/5 flex items-center justify-between gap-3"
        >
          <div class="flex items-center gap-3 min-w-0">
            <img 
              :src="work.poster_path" 
              :alt="work.title"
              class="w-12 aspect-[2/3] object-cover rounded-lg bg-cinema-800 flex-shrink-0"
              @error="$event.target.src = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80'"
            />
            <div class="min-w-0">
              <div class="text-xs font-black text-white truncate">{{ work.title }}</div>
              <div class="text-[10px] text-gray-400 flex items-center gap-1.5 flex-wrap mt-0.5">
                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase bg-white/10 text-white">{{ work.format }}</span>
                <span class="text-amber-400" v-if="work.pricing_mode !== 'free'">⭐ {{ work.price_stars }}</span>
                <span class="text-emerald-400" v-else>{{ t('free') }}</span>
                <span class="text-gray-400">• {{ work.episodes?.length || 0 }} {{ t('episodes') }}</span>
              </div>
              <div class="mt-1">
                <span 
                  class="px-2 py-0.5 rounded text-[9px] font-bold"
                  :class="work.status === 'published' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'"
                >
                  {{ work.status === 'published' ? t('status_published') : t('status_hidden') }}
                </span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col gap-1 flex-shrink-0">
            <button 
              v-if="work.status === 'published'"
              @click="togglePublish(work, false)"
              class="px-2.5 py-1 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 font-bold text-[10px] transition"
            >
              {{ t('btn_hide') }}
            </button>
            <button 
              v-else
              @click="togglePublish(work, true)"
              class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 font-bold text-[10px] transition"
            >
              {{ t('btn_publish') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: PAYMENTS -->
    <div v-if="activeTab === 'payments'" class="space-y-3">
      <h3 class="text-xs font-extrabold text-white uppercase">{{ t('recent_payments') }}</h3>
      <div v-if="payments.length === 0" class="py-8 text-center text-xs text-gray-500">
        {{ t('no_payments_yet') }}
      </div>
      <div v-else class="space-y-2">
        <div 
          v-for="pay in payments" 
          :key="pay.id"
          class="p-3 rounded-xl bg-cinema-900 border border-white/5 flex items-center justify-between text-xs"
        >
          <div>
            <div class="font-bold text-white">ID #{{ pay.id }} • TG {{ pay.telegram_payment_charge_id?.slice(0, 10) || 'Local' }}</div>
            <div class="text-[10px] text-gray-400">{{ pay.created_at ? new Date(pay.created_at).toLocaleString() : '' }}</div>
          </div>
          <div class="text-right">
            <div class="font-black text-amber-300">⭐ {{ pay.amount_stars }} Stars</div>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-400 uppercase">{{ pay.status }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: AUDIT LOGS -->
    <div v-if="activeTab === 'audit'" class="space-y-3">
      <h3 class="text-xs font-extrabold text-white uppercase">{{ t('audit_logs_title') }}</h3>
      <div v-if="auditLogs.length === 0" class="py-8 text-center text-xs text-gray-500">
        {{ t('no_logs') }}
      </div>
      <div v-else class="space-y-2">
        <div 
          v-for="log in auditLogs" 
          :key="log.id"
          class="p-3 rounded-xl bg-cinema-900 border border-white/5 text-xs space-y-1"
        >
          <div class="flex items-center justify-between">
            <span class="font-black text-cinema-gold uppercase text-[10px]">{{ log.action }}</span>
            <span class="text-[10px] text-gray-500">{{ log.created_at ? new Date(log.created_at).toLocaleTimeString() : '' }}</span>
          </div>
          <div class="text-gray-300 text-[11px]">{{ log.entity_type }} #{{ log.entity_id }}</div>
        </div>
      </div>
    </div>

    <!-- CREATE WORK MODAL -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="w-full max-w-sm rounded-2xl bg-cinema-900 border border-white/10 p-5 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-black text-white">+ {{ t('create_work_title') }}</h3>
          <button @click="showCreateModal = false" class="text-gray-400 hover:text-white text-lg">&times;</button>
        </div>

        <form @submit.prevent="createWork" class="space-y-3 text-xs">
          <div>
            <label class="block text-gray-400 text-[10px] mb-1 font-bold">{{ t('field_title') }}</label>
            <input v-model="form.title" required class="w-full px-3 py-2 rounded-xl bg-cinema-950 border border-white/10 text-white text-xs focus:outline-none focus:border-cinema-gold" />
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-gray-400 text-[10px] mb-1 font-bold">{{ t('field_format') }}</label>
              <select v-model="form.format" class="w-full px-3 py-2 rounded-xl bg-cinema-950 border border-white/10 text-white text-xs">
                <option value="film">Film</option>
                <option value="series">Series</option>
                <option value="short">Short</option>
              </select>
            </div>
            <div>
              <label class="block text-gray-400 text-[10px] mb-1 font-bold">{{ t('field_style') }}</label>
              <select v-model="form.style" class="w-full px-3 py-2 rounded-xl bg-cinema-950 border border-white/10 text-white text-xs">
                <option value="realistic">Realistic</option>
                <option value="animation_2d">Animation 2D</option>
                <option value="animation_3d">Animation 3D</option>
                <option value="mixed">Mixed</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-gray-400 text-[10px] mb-1 font-bold">{{ t('field_pricing') }}</label>
              <select v-model="form.pricing_mode" class="w-full px-3 py-2 rounded-xl bg-cinema-950 border border-white/10 text-white text-xs">
                <option value="free">Free</option>
                <option value="work">Full Work</option>
                <option value="episode">Per Episode</option>
              </select>
            </div>
            <div>
              <label class="block text-gray-400 text-[10px] mb-1 font-bold">{{ t('field_stars') }}</label>
              <input v-model.number="form.price_stars" type="number" min="0" class="w-full px-3 py-2 rounded-xl bg-cinema-950 border border-white/10 text-white text-xs" />
            </div>
          </div>

          <div>
            <label class="block text-gray-400 text-[10px] mb-1 font-bold">{{ t('field_poster_url') }}</label>
            <input v-model="form.poster_path" required placeholder="https://..." class="w-full px-3 py-2 rounded-xl bg-cinema-950 border border-white/10 text-white text-xs" />
          </div>

          <div>
            <label class="block text-gray-400 text-[10px] mb-1 font-bold">{{ t('field_description') }}</label>
            <textarea v-model="form.description" rows="3" required class="w-full px-3 py-2 rounded-xl bg-cinema-950 border border-white/10 text-white text-xs"></textarea>
          </div>

          <div class="flex gap-2 pt-2">
            <button type="button" @click="showCreateModal = false" class="flex-1 py-2 rounded-xl bg-cinema-800 text-gray-300 font-bold">
              {{ t('btn_cancel') }}
            </button>
            <button type="submit" :disabled="isSubmitting" class="flex-1 py-2 rounded-xl bg-cinema-gold text-black font-extrabold">
              {{ isSubmitting ? '...' : t('btn_save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'

const authStore = useAuthStore()

const activeTab = ref('works')
const isLoading = ref(false)
const isSubmitting = ref(false)
const alertMessage = ref('')
const showCreateModal = ref(false)

const overview = ref(null)
const works = ref([])
const payments = ref([])
const auditLogs = ref([])

const form = ref({
  title: '',
  description: '',
  format: 'film',
  style: 'realistic',
  language: 'uz',
  age_rating: '12+',
  poster_path: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&auto=format&fit=crop&q=80',
  pricing_mode: 'work',
  price_stars: 25,
})

function getHeaders() {
  const token = authStore.token || localStorage.getItem('aikino_token')
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`,
  }
}

async function loadAll() {
  isLoading.value = true
  alertMessage.value = ''
  try {
    const headers = getHeaders()

    // 1. Overview
    const resOverview = await fetch('/api/v1/admin/analytics/overview', { headers })
    if (resOverview.ok) {
      const json = await resOverview.json()
      overview.value = json.data
    }

    // 2. Works
    const resWorks = await fetch('/api/v1/admin/works', { headers })
    if (resWorks.ok) {
      const json = await resWorks.json()
      works.value = json.data?.data || json.data || []
    }

    // 3. Payments
    const resPayments = await fetch('/api/v1/admin/payments', { headers })
    if (resPayments.ok) {
      const json = await resPayments.json()
      payments.value = json.data?.data || json.data || []
    }

    // 4. Audit
    const resAudit = await fetch('/api/v1/admin/audit-logs', { headers })
    if (resAudit.ok) {
      const json = await resAudit.json()
      auditLogs.value = json.data?.data || json.data || []
    }
  } catch (err) {
    alertMessage.value = 'Yuklashda xatolik: ' + err.message
  } finally {
    isLoading.value = false
  }
}

async function togglePublish(work, shouldPublish) {
  try {
    const endpoint = shouldPublish ? `/api/v1/admin/works/${work.id}/publish` : `/api/v1/admin/works/${work.id}/hide`
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: getHeaders(),
    })
    if (res.ok) {
      work.status = shouldPublish ? 'published' : 'hidden'
      alertMessage.value = shouldPublish ? 'Asar nashr qilindi' : 'Asar yashirildi'
      loadAll()
    }
  } catch (err) {
    alertMessage.value = err.message
  }
}

async function createWork() {
  isSubmitting.value = true
  try {
    const res = await fetch('/api/v1/admin/works', {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(form.value),
    })
    if (res.ok) {
      alertMessage.value = 'Yangi asar muvaffaqiyatli yaratildi!'
      showCreateModal.value = false
      loadAll()
    } else {
      const json = await res.json()
      alertMessage.value = json.error?.message || 'Xatolik yuz berdi'
    }
  } catch (err) {
    alertMessage.value = err.message
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadAll()
})
</script>
