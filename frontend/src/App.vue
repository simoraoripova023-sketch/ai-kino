<template>
  <div class="min-h-screen bg-cinema-950 text-gray-100 flex justify-center items-start selection:bg-cinema-gold selection:text-black">
    <!-- Desktop Left Wing (Only on large screens) -->
    <aside class="hidden lg:flex flex-col justify-between w-80 h-screen sticky top-0 p-8 border-r border-white/5 select-none">
      <div class="space-y-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-cinema-gold flex items-center justify-center font-black text-black text-xl shadow-xl shadow-cinema-gold/30">
            AI
          </div>
          <div>
            <div class="font-black text-xl tracking-wider text-white">AI KINO 2.0</div>
            <div class="text-[10px] text-cinema-gold font-bold uppercase tracking-widest">Telegram Mini App</div>
          </div>
        </div>

        <p class="text-xs text-gray-400 leading-relaxed">
          100% sun‘iy intellekt texnologiyalari yordamida yaratilgan eksklyuziv badiiy filmlar, kiberpank seriallar va 3D animatsiyalar platformasi.
        </p>

        <div class="space-y-2 pt-2">
          <div class="p-3 rounded-xl bg-cinema-900 border border-white/5 flex items-center gap-3">
            <span class="text-xl">⭐</span>
            <div>
              <div class="text-xs font-bold text-white">Telegram Stars</div>
              <div class="text-[10px] text-gray-400">Xavfsiz rasmiy to‘lov tizimi</div>
            </div>
          </div>
          <div class="p-3 rounded-xl bg-cinema-900 border border-white/5 flex items-center gap-3">
            <span class="text-xl">🛡️</span>
            <div>
              <div class="text-xs font-bold text-white">Mualliflik himoyasi</div>
              <div class="text-[10px] text-gray-400">protect_content texnologiyasi</div>
            </div>
          </div>
        </div>
      </div>

      <div class="text-[11px] text-gray-500">
        © 2026 AI Kino 2.0 • Barcha huquqlar himoyalangan.
      </div>
    </aside>

    <!-- Main Mobile Container (Centered) -->
    <div class="w-full max-w-md min-h-screen bg-cinema-950 flex flex-col relative shadow-2xl border-x border-white/5 pb-32">
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
          <button @click="toggleLanguage" class="px-2 py-1 text-xs font-bold rounded border border-white/10 hover:border-cinema-gold/40 text-cinema-gold">
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
          to="/referral" 
          class="flex flex-col items-center gap-1 transition relative"
          :class="$route.path === '/referral' ? 'text-cinema-gold font-semibold' : 'text-gray-400 hover:text-gray-200'"
        >
          <div class="relative">
            <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          </div>
          <span class="text-[10px]">{{ t('friends') }}</span>
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

    <!-- Desktop Right Wing (Only on large screens) -->
    <aside class="hidden lg:flex flex-col justify-between w-80 h-screen sticky top-0 p-8 border-l border-white/5 select-none text-right">
      <div class="space-y-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Platforma imkoniyatlari</div>
        <div class="space-y-2 text-xs text-gray-300">
          <div class="p-2.5 rounded-lg bg-cinema-900/50 border border-white/5">🎬 4K va Full HD sifat</div>
          <div class="p-2.5 rounded-lg bg-cinema-900/50 border border-white/5">🍿 Ko‘p qismli 1-qism bepul</div>
          <div class="p-2.5 rounded-lg bg-cinema-900/50 border border-white/5">🇺🇿 O‘zbek va Rus tillarida</div>
          <div class="p-2.5 rounded-lg bg-cinema-900/50 border border-white/5">⚡ Tezkor yuklanish (Vite 6)</div>
        </div>
      </div>

      <div class="p-4 rounded-2xl bg-cinema-900/40 border border-cinema-gold/20 text-center space-y-2">
        <div class="text-2xl">📱</div>
        <div class="text-xs font-bold text-white">Smartfonda oching</div>
        <div class="text-[10px] text-gray-400 leading-tight">Telegram orqali Mini App ko‘rinishida to‘liq ekranli tomosha qiling.</div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { t, currentLang, toggleLanguage } from './i18n'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

// Setup Telegram Native BackButton and Theme
function setupTelegramNativeUI() {
  const tg = window.Telegram?.WebApp
  if (!tg) return

  // Theme colors
  tg.setHeaderColor?.('#0f1118')
  tg.setBackgroundColor?.('#07080b')

  // Back button handler
  if (tg.BackButton) {
    tg.BackButton.onClick(() => {
      if (route.path !== '/') {
        router.back()
      }
    })

    // Reactively toggle BackButton based on current route
    watch(
      () => route.path,
      (newPath) => {
        if (newPath === '/') {
          tg.BackButton.hide()
        } else {
          tg.BackButton.show()
        }
      },
      { immediate: true }
    )
  }
}

onMounted(() => {
  authStore.loginWithTelegram()
  setupTelegramNativeUI()
})
</script>

<style scoped>
.safe-bottom {
  padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
}
</style>
