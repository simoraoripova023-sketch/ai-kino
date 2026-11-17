<template>
  <div class="space-y-4">
    <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2">
      <span>⭐</span>
      Mening xaridlarim
    </h2>

    <div v-if="isLoading" class="space-y-3">
      <div v-for="n in 3" :key="n" class="h-20 bg-cinema-900 animate-pulse rounded-xl"></div>
    </div>

    <div v-else-if="purchases.length === 0" class="py-16 text-center text-gray-400 space-y-3">
      <div class="text-4xl">🎟️</div>
      <p class="text-xs font-semibold">Sizda hali pullik xaridlar mavjud emas</p>
      <router-link
        to="/catalog"
        class="inline-block px-4 py-2 rounded-xl bg-cinema-gold text-black font-extrabold text-xs"
      >
        Katalogni ko‘rish
      </router-link>
    </div>

    <div v-else class="space-y-2.5">
      <div
        v-for="p in purchases"
        :key="p.id"
        class="p-3.5 rounded-xl bg-cinema-900 border border-white/5 flex items-center justify-between"
      >
        <div>
          <div class="text-xs font-bold text-white">
            {{ p.intent?.target_type === 'work' ? 'To‘liq asar xaridi' : 'Epizod xaridi' }}
          </div>
          <div class="text-[10px] text-gray-400 mt-0.5">
            {{ new Date(p.paid_at).toLocaleDateString('uz-UZ') }} • ⭐ {{ p.amount_stars }} Stars
          </div>
        </div>
        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
          Faol
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'

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
