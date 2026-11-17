<template>
  <div class="space-y-4">
    <!-- Search Bar -->
    <div class="relative">
      <input
        v-model="catalogStore.searchQuery"
        @input="onSearch"
        type="text"
        :placeholder="t('search_placeholder')"
        class="w-full bg-cinema-900 border border-white/10 rounded-xl px-4 py-2.5 pl-10 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-cinema-gold/60 transition"
      />
      <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
      <button 
        v-if="catalogStore.searchQuery" 
        @click="clearSearch" 
        class="absolute right-3 top-2.5 text-xs text-gray-400 hover:text-white"
      >
        ✕
      </button>
    </div>

    <!-- Format Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
      <button
        v-for="tab in formatTabs"
        :key="tab.value"
        @click="selectFormat(tab.value)"
        class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition"
        :class="catalogStore.selectedFormat === tab.value ? 'bg-cinema-gold text-black shadow-md shadow-cinema-gold/20' : 'bg-cinema-900 text-gray-400 hover:text-white border border-white/5'"
      >
        {{ t(tab.key) }}
      </button>
    </div>

    <!-- Genre Pills -->
    <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
      <button
        @click="selectGenre('')"
        class="px-2.5 py-1 rounded-full text-[11px] font-semibold whitespace-nowrap border transition"
        :class="!catalogStore.selectedGenre ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-900'"
      >
        {{ t('all_genres') }}
      </button>
      <button
        v-for="genre in catalogStore.genres"
        :key="genre.id"
        @click="selectGenre(genre.slug)"
        class="px-2.5 py-1 rounded-full text-[11px] font-semibold whitespace-nowrap border transition"
        :class="catalogStore.selectedGenre === genre.slug ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-900'"
      >
        {{ currentLang === 'ru' ? genre.name_ru : genre.name_uz }}
      </button>
    </div>

    <!-- Works Grid -->
    <div v-if="catalogStore.isLoading" class="grid grid-cols-2 gap-3">
      <div v-for="n in 4" :key="n" class="aspect-[2/3] bg-cinema-900 animate-pulse rounded-xl"></div>
    </div>

    <div v-else-if="catalogStore.works.length === 0" class="py-16 text-center text-gray-400">
      <div class="text-3xl mb-2">🎬</div>
      <p class="text-xs font-semibold">{{ t('no_results') }}</p>
    </div>

    <div v-else class="grid grid-cols-2 gap-3">
      <router-link
        v-for="work in catalogStore.works"
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
</template>

<script setup>
import { onMounted } from 'vue'
import { useCatalogStore } from '../stores/catalog'
import { t, currentLang } from '../i18n'

const catalogStore = useCatalogStore()

const formatTabs = [
  { key: 'all', value: '' },
  { key: 'films', value: 'film' },
  { key: 'series', value: 'series' },
  { key: 'shorts', value: 'short' },
]

function selectFormat(fmt) {
  catalogStore.selectedFormat = fmt
  catalogStore.fetchWorks()
}

function selectGenre(slug) {
  catalogStore.selectedGenre = slug
  catalogStore.fetchWorks()
}

let timeout = null
function onSearch() {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    catalogStore.fetchWorks()
  }, 300)
}

function clearSearch() {
  catalogStore.searchQuery = ''
  catalogStore.fetchWorks()
}

onMounted(() => {
  catalogStore.fetchGenres()
  catalogStore.fetchWorks()
})
</script>
