<template>
  <div class="space-y-5">
    <!-- User Card -->
    <div class="p-4 rounded-2xl bg-cinema-900 border border-white/5 flex items-center gap-3">
      <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-cinema-gold to-amber-600 flex items-center justify-center font-black text-black text-lg shadow-lg shadow-cinema-gold/20">
        {{ (authStore.user?.first_name || 'U').charAt(0).toUpperCase() }}
      </div>
      <div class="flex-1">
        <div class="flex items-center gap-2">
          <h3 class="text-sm font-black text-white leading-tight">
            {{ authStore.user?.first_name || t('guest_user') }}
          </h3>
          <span v-if="authStore.user?.is_admin" class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-cinema-gold text-black">
            ADMIN
          </span>
        </div>
        <p class="text-xs text-gray-400">
          {{ authStore.user?.username ? '@' + authStore.user.username : 'ID: ' + (authStore.user?.telegram_id || t('local_mode')) }}
        </p>
      </div>
    </div>

    <!-- Admin Link (if is_admin) -->
    <div v-if="authStore.user?.is_admin" class="p-4 rounded-2xl bg-gradient-to-r from-amber-500/15 via-cinema-900 to-cinema-900 border border-cinema-gold/40 flex items-center justify-between">
      <div>
        <div class="text-xs font-black text-cinema-gold uppercase flex items-center gap-1.5">
          <span>👑</span> {{ t('admin_panel_title') }}
        </div>
        <div class="text-[10px] text-gray-400">{{ t('admin_panel_sub') }}</div>
      </div>
      <router-link to="/admin" class="px-3 py-1.5 rounded-xl bg-cinema-gold text-black font-black text-xs shadow-md hover:opacity-90 active:scale-95 transition">
        {{ t('btn_open') }} &rarr;
      </router-link>
    </div>

    <!-- Local Sandbox Role Switcher -->
    <div v-if="!authStore.rawInitData" class="p-4 rounded-2xl bg-cinema-900 border border-white/5 space-y-2">
      <div class="flex items-center justify-between">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ t('sandbox_mode_title') }}</h4>
        <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold">Lokal rejim</span>
      </div>
      <p class="text-[10px] text-gray-400">{{ t('sandbox_mode_desc') }}</p>
      <div class="grid grid-cols-2 gap-2 pt-1">
        <button
          @click="authStore.loginAs(123456789)"
          class="py-2 px-3 rounded-xl text-xs font-bold border transition text-left"
          :class="!authStore.user?.is_admin ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-800'"
        >
          <div>👤 {{ t('role_viewer') }}</div>
          <div class="text-[9px] text-gray-400 font-normal">ID: 123456789</div>
        </button>
        <button
          @click="authStore.loginAs(999999999)"
          class="py-2 px-3 rounded-xl text-xs font-bold border transition text-left"
          :class="authStore.user?.is_admin ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-800'"
        >
          <div>👑 {{ t('role_admin') }}</div>
          <div class="text-[9px] text-gray-400 font-normal">ID: 999999999</div>
        </button>
      </div>
    </div>

    <!-- Language Selector -->
    <div class="p-4 rounded-2xl bg-cinema-900 border border-white/5 space-y-2.5">
      <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ t('interface_language') }}</h4>
      <div class="grid grid-cols-2 gap-2">
        <button
          @click="setLanguage('uz')"
          class="py-2 rounded-xl text-xs font-bold border transition"
          :class="currentLang === 'uz' ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-800'"
        >
          {{ t('lang_uz') }}
        </button>
        <button
          @click="setLanguage('ru')"
          class="py-2 rounded-xl text-xs font-bold border transition"
          :class="currentLang === 'ru' ? 'border-cinema-gold text-cinema-gold bg-cinema-gold/10' : 'border-white/5 text-gray-400 bg-cinema-800'"
        >
          {{ t('lang_ru') }}
        </button>
      </div>
    </div>

    <!-- Platform Guidelines -->
    <div class="p-4 rounded-2xl bg-cinema-900 border border-white/5 space-y-2 text-xs text-gray-300">
      <h4 class="font-bold text-white uppercase tracking-wider">{{ t('platform_rules_title') }}</h4>
      <p class="text-[11px] leading-relaxed text-gray-400">
        {{ t('rule_1') }}<br/>
        {{ t('rule_2') }}<br/>
        {{ t('rule_3') }}
      </p>
    </div>

    <!-- Version & Info -->
    <div class="text-center pt-2 text-[10px] text-gray-500 space-y-1">
      <div>{{ t('app_version') }}</div>
      <div>{{ t('app_subtitle') }}</div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { t, currentLang, setLanguage } from '../i18n'

const authStore = useAuthStore()
</script>
