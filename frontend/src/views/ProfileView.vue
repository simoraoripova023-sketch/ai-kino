<template>
  <div class="space-y-5">
    <!-- User Card -->
    <div class="p-4 rounded-2xl bg-cinema-900 border border-white/5 flex items-center gap-3">
      <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-cinema-gold to-amber-600 flex items-center justify-center font-black text-black text-lg shadow-lg shadow-cinema-gold/20">
        {{ (authStore.user?.first_name || 'U').charAt(0).toUpperCase() }}
      </div>
      <div class="flex-1">
        <h3 class="text-sm font-black text-white leading-tight">
          {{ authStore.user?.first_name || 'Mehmon tomoshabin' }}
        </h3>
        <p class="text-xs text-gray-400">
          {{ authStore.user?.username ? '@' + authStore.user.username : 'ID: ' + (authStore.user?.telegram_id || 'Lokal rejim') }}
        </p>
      </div>
    </div>

    <!-- Language Selector -->
    <div class="p-4 rounded-2xl bg-cinema-900 border border-white/5 space-y-2.5">
      <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Interfeys tili</h4>
      <div class="grid grid-cols-2 gap-2">
        <button
          @click="setLang('uz')"
          class="py-2 rounded-xl text-xs font-bold border transition"
          :class="currentLang === 'uz' ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-800'"
        >
          🇺🇿 O‘zbekcha
        </button>
        <button
          @click="setLang('ru')"
          class="py-2 rounded-xl text-xs font-bold border transition"
          :class="currentLang === 'ru' ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-800'"
        >
          🇷🇺 Русский
        </button>
      </div>
    </div>

    <!-- Platform Guidelines -->
    <div class="p-4 rounded-2xl bg-cinema-900 border border-white/5 space-y-2 text-xs text-gray-300">
      <h4 class="font-bold text-white uppercase tracking-wider">Platforma qoidalari</h4>
      <p class="text-[11px] leading-relaxed text-gray-400">
        • Barcha kontent 100% sun‘iy intellekt texnologiyalari yordamida yaratilgan.<br/>
        • Yosh toifalari: 0+, 6+, 12+, 16+. 18+ kontent qat‘iyan taqiqlangan.<br/>
        • To‘lovlar Telegram Stars (XTR) orqali rasmiy Telegram to‘lov tizimida amalga oshiriladi.
      </p>
    </div>

    <!-- Version & Info -->
    <div class="text-center pt-2 text-[10px] text-gray-500 space-y-1">
      <div>AI Kino 2.0 • Versiya: 2.0.0 (MVP)</div>
      <div>Telegram Mini App + Bot Integration</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const currentLang = ref(localStorage.getItem('aikino_lang') || 'uz')

function setLang(lang) {
  currentLang.value = lang
  localStorage.setItem('aikino_lang', lang)
  window.location.reload()
}
</script>
