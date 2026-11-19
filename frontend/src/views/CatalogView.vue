<template>
  <div class="space-y-4">
    <!-- Search Bar -->
    <div class="relative">
      <input
        v-model="catalogStore.searchQuery"
        @input="onSearch"
        type="text"
        :placeholder="t('search_placeholder')"
        class="w-full bg-cinema-900 border border-white/10 rounded-xl px-4 py-2.5 pl-10 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-cinema-gold/60 transition shadow-inner"
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

    <!-- Format Tabs (Film / Series / Short) -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
      <button
        v-for="tab in formatTabs"
        :key="tab.value"
        @click="selectFormat(tab.value)"
        class="px-3.5 py-1.5 rounded-lg text-xs font-black whitespace-nowrap transition"
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

    <!-- Style and Age Rating Controls -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-[11px]">
      <!-- Sort Selector Dropdown -->
      <select 
        v-model="catalogStore.selectedSort" 
        @change="catalogStore.fetchWorks()"
        class="bg-cinema-900 border border-white/10 text-white rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:border-cinema-gold font-bold"
      >
        <option value="newest">🕒 {{ t('sort_newest') }}</option>
        <option value="stars_asc">⭐ {{ t('sort_stars_asc') }}</option>
        <option value="stars_desc">⭐ {{ t('sort_stars_desc') }}</option>
        <option value="title">🔤 {{ t('sort_title') }}</option>
      </select>

      <!-- Style Filter -->
      <select 
        v-model="catalogStore.selectedStyle" 
        @change="catalogStore.fetchWorks()"
        class="bg-cinema-900 border border-white/10 text-white rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:border-cinema-gold font-medium"
      >
        <option value="">🎨 {{ t('style_all') }}</option>
        <option value="realistic">{{ t('style_realistic') }}</option>
        <option value="animation_2d">{{ t('style_animation_2d') }}</option>
        <option value="animation_3d">{{ t('style_animation_3d') }}</option>
        <option value="mixed">{{ t('style_mixed') }}</option>
      </select>

      <!-- Age Rating Filter -->
      <select 
        v-model="catalogStore.selectedAgeRating" 
        @change="catalogStore.fetchWorks()"
        class="bg-cinema-900 border border-white/10 text-white rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:border-cinema-gold font-medium"
      >
        <option value="">🔞 {{ t('filter_age') }}: Barchasi</option>
        <option value="0+">0+</option>
        <option value="6+">6+</option>
        <option value="12+">12+</option>
        <option value="16+">16+</option>
      </select>
    </div>

    <!-- Works Grid -->
    <div v-if="catalogStore.isLoading" class="grid grid-cols-2 gap-3">
      <div v-for="n in 6" :key="n" class="aspect-[2/3] bg-cinema-900 animate-pulse rounded-xl border border-white/5"></div>
    </div>

    <div v-else-if="catalogStore.works.length === 0" class="py-16 text-center text-gray-400 bg-cinema-900/40 rounded-2xl border border-white/5 p-6">
      <div class="text-3xl mb-2">🎬</div>
      <p class="text-xs font-semibold mb-3">{{ t('no_results') }}</p>
      <button 
        @click="resetAllFilters"
        class="px-3.5 py-1.5 rounded-xl bg-cinema-800 hover:bg-cinema-700 text-cinema-gold font-bold text-xs transition"
      >
        Filtrlarni tozalash
      </button>
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
            <span class="capitalize">{{ work.style?.replace('_', ' ') }}</span>
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

function resetAllFilters() {
  catalogStore.selectedFormat = ''
  catalogStore.selectedStyle = ''
  catalogStore.selectedGenre = ''
  catalogStore.selectedAgeRating = ''
  catalogStore.selectedPricingMode = ''
  catalogStore.selectedSort = 'newest'
  catalogStore.searchQuery = ''
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
