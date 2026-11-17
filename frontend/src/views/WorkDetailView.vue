<template>
  <div v-if="catalogStore.currentWork" class="space-y-5 pb-8">
    <!-- Back button & Cover/Poster -->
    <div class="relative rounded-2xl overflow-hidden aspect-[16/10] bg-cinema-900 border border-white/10 shadow-2xl">
      <img
        :src="catalogStore.currentWork.cover_path || catalogStore.currentWork.poster_path"
        :alt="catalogStore.currentWork.title"
        class="w-full h-full object-cover brightness-75"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-cinema-950 via-cinema-950/50 to-transparent"></div>

      <router-link
        to="/catalog"
        class="absolute top-3 left-3 w-8 h-8 rounded-full bg-black/60 backdrop-blur border border-white/10 flex items-center justify-center text-white hover:bg-black/80 transition"
      >
        &larr;
      </router-link>

      <div class="absolute bottom-3 left-3 right-3 flex items-end gap-3">
        <img
          :src="catalogStore.currentWork.poster_path"
          :alt="catalogStore.currentWork.title"
          class="w-20 aspect-[2/3] object-cover rounded-lg border-2 border-white/20 shadow-2xl flex-shrink-0"
          @error="$event.target.src = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80'"
        />
        <div class="flex-1">
          <div class="flex items-center gap-1.5 mb-1 flex-wrap">
            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-cinema-gold text-black">
              {{ catalogStore.currentWork.format }}
            </span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-white/10 text-white">
              {{ catalogStore.currentWork.age_rating }}
            </span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-white/5 text-gray-300 capitalize">
              {{ catalogStore.currentWork.style.replace('_', ' ') }}
            </span>
          </div>
          <h1 class="text-lg font-black text-white leading-tight">
            {{ catalogStore.currentWork.title }}
          </h1>
        </div>
      </div>
    </div>

    <!-- Description & Genres -->
    <div class="space-y-2">
      <div class="flex gap-1.5 flex-wrap">
        <span
          v-for="g in catalogStore.currentWork.genres"
          :key="g.id"
          class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-cinema-900 border border-white/10 text-cinema-gold"
        >
          #{{ g.name_uz }}
        </span>
      </div>
      <p class="text-xs text-gray-300 leading-relaxed">
        {{ catalogStore.currentWork.description }}
      </p>
    </div>

    <!-- Full Work Purchase Action (if pricing_mode === 'work') -->
    <div 
      v-if="catalogStore.currentWork.pricing_mode === 'work' && !isWholeWorkOwned"
      class="p-4 rounded-xl bg-gradient-to-br from-amber-500/10 via-cinema-900 to-cinema-950 border border-cinema-gold/30 space-y-2"
    >
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs font-bold text-white">To‘liq asarni xarid qilish</div>
          <div class="text-[10px] text-gray-400">Barcha qismlarni bir marta oching</div>
        </div>
        <div class="text-sm font-black text-cinema-gold flex items-center gap-1">
          ⭐ {{ catalogStore.currentWork.price_stars }} Stars
        </div>
      </div>
      <button
        @click="buyTarget('work', catalogStore.currentWork.id)"
        :disabled="isBuying"
        class="w-full py-2.5 rounded-xl bg-gradient-to-r from-cinema-gold to-amber-500 text-black font-black text-xs flex items-center justify-center gap-1.5 hover:opacity-95 active:scale-[0.98] transition shadow-lg shadow-cinema-gold/20"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <span>{{ isBuying ? 'Hisob yaratilmoqda...' : 'Telegram Stars bilan ochish' }}</span>
      </button>
    </div>

    <!-- Notice Box / Toast Message -->
    <div v-if="toastMessage" class="p-3 rounded-xl bg-cinema-800 border border-cinema-gold/40 text-xs font-medium text-cinema-gold flex items-center justify-between animate-fade-in">
      <span>{{ toastMessage }}</span>
      <button @click="toastMessage = ''" class="text-white ml-2">&times;</button>
    </div>

    <!-- Episodes List -->
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-white uppercase tracking-wide">
          Qismlar ({{ catalogStore.episodes.length }})
        </h3>
      </div>

      <div class="space-y-2">
        <div
          v-for="ep in catalogStore.episodes"
          :key="ep.id"
          class="p-3 rounded-xl bg-cinema-900 border border-white/5 flex items-center justify-between gap-3 hover:border-white/10 transition"
        >
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-cinema-800 flex items-center justify-center font-black text-xs text-cinema-gold flex-shrink-0">
              {{ ep.number }}
            </div>
            <div>
              <div class="text-xs font-bold text-white">{{ ep.title || (ep.number + '-qism') }}</div>
              <div class="text-[10px] text-gray-400 flex items-center gap-2">
                <span>{{ Math.round(ep.duration_sec / 60) }} daqiqa</span>
                <span v-if="ep.access_state === 'free'" class="text-emerald-400 font-semibold">• Bepul</span>
                <span v-else-if="ep.access_state === 'owned'" class="text-blue-400 font-semibold">• Ochilgan</span>
                <span v-else class="text-amber-400 font-semibold">• ⭐ {{ ep.price_stars }} Stars</span>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <div>
            <button
              v-if="ep.access_state === 'free' || ep.access_state === 'owned'"
              @click="playEpisode(ep.id)"
              :disabled="isPlaying"
              class="px-3.5 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs flex items-center gap-1 active:scale-95 transition shadow-md shadow-emerald-500/20"
            >
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
              <span>Ko‘rish</span>
            </button>

            <button
              v-else
              @click="buyTarget('episode', ep.id)"
              :disabled="isBuying"
              class="px-3 py-1.5 rounded-lg bg-cinema-gold hover:bg-amber-400 text-black font-extrabold text-xs flex items-center gap-1 active:scale-95 transition shadow-md shadow-cinema-gold/20"
            >
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17a2 2 0 100-4 2 2 0 000 4zm6-9a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V10a2 2 0 012-2h1V6a5 5 0 0110 0v2h1zm-6-5a3 3 0 00-3 3v2h6V6a3 3 0 00-3-3z"/></svg>
              <span>Ochish</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCatalogStore } from '../stores/catalog'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const catalogStore = useCatalogStore()
const authStore = useAuthStore()

const isPlaying = ref(false)
const isBuying = ref(false)
const toastMessage = ref('')

const isWholeWorkOwned = computed(() => {
  return catalogStore.episodes.length > 0 && catalogStore.episodes.every(ep => ep.access_state === 'owned')
})

async function playEpisode(id) {
  isPlaying.value = true
  toastMessage.value = ''
  try {
    const token = authStore.token || localStorage.getItem('aikino_token')
    const res = await fetch(`/api/v1/episodes/${id}/play`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })
    const json = await res.json()
    if (res.ok) {
      toastMessage.value = '🎬 Video bot chatiga yuborildi! Telegram chatiga o‘ting.'
      // Trigger native Telegram haptic if available
      window.Telegram?.WebApp?.HapticFeedback?.notificationOccurred('success')
    } else {
      toastMessage.value = json.error?.message || 'Xatolik yuz berdi'
    }
  } catch (err) {
    toastMessage.value = 'Xatolik: ' + err.message
  } finally {
    isPlaying.value = false
  }
}

async function buyTarget(targetType, targetId) {
  isBuying.value = true
  toastMessage.value = ''
  try {
    const token = authStore.token || localStorage.getItem('aikino_token')
    const res = await fetch('/api/v1/purchase-intents', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        target_type: targetType,
        target_id: targetId,
      }),
    })
    const json = await res.json()
    if (res.ok) {
      toastMessage.value = '⭐ To‘lov hisobi Telegram bot chatiga yuborildi!'
      window.Telegram?.WebApp?.HapticFeedback?.impactOccurred('medium')
    } else {
      toastMessage.value = json.error?.message || 'To‘lov yaratishda xatolik'
    }
  } catch (err) {
    toastMessage.value = 'Xatolik: ' + err.message
  } finally {
    isBuying.value = false
  }
}

onMounted(() => {
  catalogStore.fetchWorkDetail(route.params.id)
})
</script>
