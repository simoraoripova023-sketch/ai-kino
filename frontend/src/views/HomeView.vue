<template>
  <div class="space-y-6">
    <!-- Hero / Featured Banner -->
    <div v-if="featuredWork" class="relative rounded-2xl overflow-hidden aspect-[16/10] bg-cinema-900 border border-white/10 group shadow-2xl">
      <img 
        :src="featuredWork.cover_path || featuredWork.poster_path" 
        :alt="featuredWork.title"
        class="w-full h-full object-cover group-hover:scale-105 transition duration-500 brightness-75"
        @error="$event.target.src = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80'"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-cinema-950 via-cinema-950/40 to-transparent flex flex-col justify-end p-4">
        <div class="flex items-center gap-2 mb-1">
          <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-cinema-gold text-black">
            {{ featuredWork.format }}
          </span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-white backdrop-blur">
            {{ featuredWork.age_rating }}
          </span>
          <span v-if="featuredWork.pricing_mode === 'free'" class="text-[10px] font-bold text-emerald-400">
            {{ t('free') }}
          </span>
          <span v-else class="text-[10px] font-bold text-amber-300 flex items-center gap-0.5">
            ⭐ {{ featuredWork.price_stars }} {{ t('stars') }}
          </span>
        </div>
        <h2 class="text-xl font-black text-white leading-tight mb-1">{{ featuredWork.title }}</h2>
        <p class="text-xs text-gray-300 line-clamp-2 mb-3">{{ featuredWork.description }}</p>

        <router-link 
          :to="`/work/${featuredWork.id}`"
          class="w-full py-2.5 rounded-xl bg-gradient-to-r from-cinema-gold to-amber-500 text-black font-extrabold text-xs flex items-center justify-center gap-2 shadow-lg shadow-cinema-gold/30 hover:opacity-90 active:scale-[0.98] transition"
        >
          <svg class="w-4 h-4" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
          {{ t('watch_now') }}
        </router-link>
      </div>
    </div>

    <!-- Continue Watching Section -->
    <div v-if="catalogStore.progressList.length > 0" class="space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-white tracking-wide uppercase flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-cinema-gold animate-pulse"></span>
          {{ t('continue_watching') }}
        </h3>
      </div>
      <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-none">
        <router-link
          v-for="item in catalogStore.progressList"
          :key="item.work_id"
          :to="`/work/${item.work_id}`"
          class="flex-shrink-0 w-36 rounded-xl overflow-hidden bg-cinema-900 border border-white/5 group"
        >
          <div class="aspect-[2/3] relative overflow-hidden bg-cinema-800">
            <img 
              :src="item.poster_path" 
              :alt="item.work_title" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              @error="$event.target.src = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80'"
            />
            <div class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-black/75 text-cinema-gold">
              {{ t('episode_num', { n: item.last_episode_number }) }}
            </div>
          </div>
          <div class="p-2">
            <div class="text-xs font-bold text-white truncate">{{ item.work_title }}</div>
            <div class="text-[10px] text-gray-400">{{ t('last_watched') }}</div>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Trending AI Works (Grid) -->
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-white tracking-wide uppercase">
          {{ t('trending_ai') }}
        </h3>
        <router-link to="/catalog" class="text-xs font-semibold text-cinema-gold hover:underline">
          {{ t('view_all') }} &rarr;
        </router-link>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <router-link
          v-for="work in catalogStore.works.slice(0, 6)"
          :key="work.id"
          :to="`/work/${work.id}`"
          class="rounded-xl overflow-hidden bg-cinema-900 border border-white/5 flex flex-col group hover:border-cinema-gold/30 transition shadow-lg"
        >
          <div class="aspect-[2/3] relative overflow-hidden bg-cinema-800">
            <img 
              :src="work.poster_path" 
              :alt="work.title" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300" 
              loading="lazy" 
              @error="$event.target.src = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80'"
            />
            <div class="absolute top-2 left-2 px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-black/60 backdrop-blur text-white">
              {{ work.format }}
            </div>
            <div v-if="work.pricing_mode === 'free'" class="absolute bottom-2 left-2 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/90 text-white">
              {{ t('free') }}
            </div>
            <div v-else class="absolute bottom-2 left-2 px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/90 text-black flex items-center gap-0.5">
              ⭐ {{ work.price_stars }}
            </div>
          </div>
          <div class="p-2.5 flex-1 flex flex-col justify-between">
            <h4 class="text-xs font-bold text-white leading-tight line-clamp-1 group-hover:text-cinema-gold transition">
              {{ work.title }}
            </h4>
            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
              <span>{{ work.age_rating }}</span>
              <span class="capitalize">{{ work.style.replace('_', ' ') }}</span>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useCatalogStore } from '../stores/catalog'
import { t } from '../i18n'

const catalogStore = useCatalogStore()

const featuredWork = computed(() => {
  return catalogStore.works.find(w => w.cover_path) || catalogStore.works[0] || null
})

onMounted(() => {
  catalogStore.fetchWorks()
  catalogStore.fetchProgress()
})
</script>
