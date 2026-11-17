import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('aikino_token') || null)
  const user = ref(JSON.parse(localStorage.getItem('aikino_user') || 'null'))
  const rawInitData = ref('')
  const isLoading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  // Initialize Telegram WebApp
  function initTelegram() {
    const tg = window.Telegram?.WebApp
    if (tg) {
      tg.ready()
      tg.expand()
      // Enable closing confirmation for better UX
      tg.enableClosingConfirmation?.()
      rawInitData.value = tg.initData || ''
    }
  }

  async function loginWithTelegram() {
    initTelegram()
    if (!rawInitData.value) {
      // Dev mode fallback
      console.warn('No Telegram initData found, running in demo/sandbox mode')
      return
    }

    isLoading.value = true
    error.value = null
    try {
      const response = await fetch('/api/v1/auth/telegram', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ init_data: rawInitData.value }),
      })

      if (!response.ok) {
        throw new Error('Telegram autentifikatsiyasi amalga oshmadi')
      }

      const res = await response.json()
      token.value = res.data.token
      user.value = res.data.user
      localStorage.setItem('aikino_token', token.value)
      localStorage.setItem('aikino_user', JSON.stringify(user.value))
    } catch (err) {
      error.value = err.message
      console.error('Auth error:', err)
    } finally {
      isLoading.value = false
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('aikino_token')
    localStorage.removeItem('aikino_user')
  }

  return {
    token,
    user,
    rawInitData,
    isLoading,
    error,
    isAuthenticated,
    initTelegram,
    loginWithTelegram,
    logout,
  }
})
