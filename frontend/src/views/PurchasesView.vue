<template>
  <div class="space-y-4">
    <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2">
      <span>⭐</span>
      {{ t('my_purchases_title') }}
    </h2>

    <div v-if="isLoading" class="space-y-3">
      <div v-for="n in 3" :key="n" class="h-24 bg-cinema-900 animate-pulse rounded-xl border border-white/5"></div>
    </div>

    <div v-else-if="purchases.length === 0" class="py-16 text-center text-gray-400 space-y-3 bg-cinema-900/40 rounded-2xl border border-white/5 p-6">
      <div class="text-4xl">🎟️</div>
      <p class="text-xs font-semibold">{{ t('no_purchases') }}</p>
      <router-link
        to="/catalog"
        class="inline-block px-4 py-2 rounded-xl bg-cinema-gold text-black font-extrabold text-xs shadow-md hover:opacity-90 active:scale-95 transition"
      >
        {{ t('btn_browse_catalog') }}
      </router-link>
    </div>

    <div v-else class="space-y-2.5">
      <div
        v-for="p in purchases"
        :key="p.id"
        class="p-3.5 rounded-xl bg-cinema-900 border border-white/5 flex items-center justify-between gap-3 hover:border-cinema-gold/30 transition shadow-lg"
      >
        <div class="flex items-center gap-3 min-w-0">
          <img
            :src="p.intent?.target_poster || 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&auto=format&fit=crop&q=80'"
            :alt="p.intent?.target_title"
            class="w-12 aspect-[2/3] object-cover rounded-lg bg-cinema-800 flex-shrink-0"
            @error="$event.target.src = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80'"
          />
          <div class="min-w-0">
            <div class="text-xs font-black text-white truncate">
              {{ p.intent?.target_title || (p.intent?.target_type === 'work' ? t('work_purchase') : t('episode_purchase')) }}
            </div>
            <div class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
              <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/10 text-amber-300">
                ⭐ {{ p.amount_stars }} Stars
              </span>
              <span>• {{ new Date(p.paid_at).toLocaleDateString(currentLang === 'ru' ? 'ru-RU' : 'uz-UZ') }}</span>
            </div>
            <div class="mt-1">
              <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                {{ t('badge_active') }}
              </span>
            </div>
          </div>
        </div>

        <router-link
          v-if="p.intent?.work_id"
          :to="`/work/${p.intent.work_id}`"
          class="px-3 py-1.5 rounded-xl bg-cinema-gold text-black font-extrabold text-[11px] flex-shrink-0 hover:opacity-90 active:scale-95 transition shadow"
        >
          {{ t('btn_watch') }} &rarr;
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { t, currentLang } from '../i18n'

const authStore = useAuthStore()
const purchases = ref([])
const isLoading = ref(false)

async function fetchPurchases() {
  const token = authStore.token || localStorage.getItem('aikino_token')
  if (!token) return

  isLoading.value = true
  try {
    const res = await fetch('/api/v1/me/purchases', {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (res.ok) {
      const json = await res.json()
      purchases.value = json.data || []
    }
  } catch (err) {
    console.error('Fetch purchases error:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchPurchases()
})
</script>
