<template>
  <div class="flex flex-col min-h-screen bg-cinema-950 text-gray-100 max-w-md mx-auto relative shadow-2xl pb-24">
    <!-- Top Header -->
    <header class="sticky top-0 z-40 bg-cinema-900/90 backdrop-blur-md border-b border-white/5 px-4 py-3 flex items-center justify-between">
      <router-link to="/" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber-500 to-cinema-gold flex items-center justify-center font-black text-black text-lg shadow-lg shadow-cinema-gold/20">
          AI
        </div>
        <span class="font-extrabold text-lg tracking-wider bg-gradient-to-r from-white via-gray-200 to-cinema-gold bg-clip-text text-transparent">
          KINO 2.0
        </span>
      </router-link>

      <div class="flex items-center gap-2">
        <router-link to="/catalog" class="p-2 rounded-full hover:bg-white/5 text-gray-400 hover:text-white transition">
          <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </router-link>
        <button @click="toggleLang" class="px-2 py-1 text-xs font-bold rounded border border-white/10 hover:border-cinema-gold/40 text-cinema-gold">
          {{ currentLang.toUpperCase() }}
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 px-4 py-4">
      <router-view />
    </main>

    <!-- Bottom Navigation Bar (Telegram WebApp Native Style) -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 glass-nav max-w-md mx-auto px-6 py-2 flex items-center justify-between safe-bottom">
      <router-link 
        to="/" 
        class="flex flex-col items-center gap-1 transition"
        :class="$route.path === '/' ? 'text-cinema-gold font-semibold' : 'text-gray-400 hover:text-gray-200'"
      >
        <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="text-[10px]">{{ t('home') }}</span>
      </router-link>

      <router-link 
        to="/catalog" 
        class="flex flex-col items-center gap-1 transition"
        :class="$route.path === '/catalog' ? 'text-cinema-gold font-semibold' : 'text-gray-400 hover:text-gray-200'"
      >
        <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
        <span class="text-[10px]">{{ t('catalog') }}</span>
      </router-link>

      <router-link 
        to="/purchases" 
        class="flex flex-col items-center gap-1 transition"
        :class="$route.path === '/purchases' ? 'text-cinema-gold font-semibold' : 'text-gray-400 hover:text-gray-200'"
      >
        <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-[10px]">{{ t('purchases') }}</span>
      </router-link>

      <router-link 
        to="/profile" 
        class="flex flex-col items-center gap-1 transition"
        :class="$route.path === '/profile' ? 'text-cinema-gold font-semibold' : 'text-gray-400 hover:text-gray-200'"
      >
        <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        <span class="text-[10px]">{{ t('profile') }}</span>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
const currentLang = ref(localStorage.getItem('aikino_lang') || 'uz')

const translations = {
  uz: {
    home: 'Asosiy',
    catalog: 'Katalog',
    purchases: 'Xaridlar',
    profile: 'Profil',
  },
  ru: {
    home: 'Главная',
    catalog: 'Каталог',
    purchases: 'Покупки',
    profile: 'Профиль',
  }
}

function t(key) {
  return translations[currentLang.value]?.[key] || key
}

function toggleLang() {
  currentLang.value = currentLang.value === 'uz' ? 'ru' : 'uz'
  localStorage.setItem('aikino_lang', currentLang.value)
}

onMounted(() => {
  authStore.loginWithTelegram()
})
</script>

<style scoped>
.safe-bottom {
  padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
}
</style>
