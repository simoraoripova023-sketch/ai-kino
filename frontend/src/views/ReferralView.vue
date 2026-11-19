<template>
  <div class="space-y-6 pb-20">
    <!-- Header Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500/20 via-primary/20 to-purple-900/30 border border-amber-500/30 p-6 shadow-2xl backdrop-blur-md">
      <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>
      
      <div class="flex items-center gap-3 mb-2">
        <span class="text-3xl">🎁</span>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-white">Do'stlarni taklif qiling</h1>
          <p class="text-xs text-amber-200/80 font-medium">Har bir taklif uchun Telegram Stars ishlang!</p>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3">
        <div class="rounded-xl bg-black/40 border border-white/10 p-3 text-center">
          <p class="text-xs text-slate-400">Stars Balansi</p>
          <div class="flex items-center justify-center gap-1.5 mt-1">
            <span class="text-2xl font-black text-amber-400">{{ stats.bonus_stars }}</span>
            <span class="text-lg">⭐️</span>
          </div>
        </div>

        <div class="rounded-xl bg-black/40 border border-white/10 p-3 text-center">
          <p class="text-xs text-slate-400">Do'stlaringiz</p>
          <div class="flex items-center justify-center gap-1.5 mt-1">
            <span class="text-2xl font-black text-purple-400">{{ stats.referrals_count }}</span>
            <span class="text-lg">👥</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Referral Link & Sharing Card -->
    <div class="rounded-2xl bg-surface border border-white/10 p-5 space-y-4 shadow-xl">
      <h2 class="text-base font-bold text-white flex items-center gap-2">
        <span>🔗</span> Shaxsiy taklif havolangiz
      </h2>

      <div class="flex items-center gap-2 bg-black/50 border border-white/10 rounded-xl p-2.5">
        <input
          type="text"
          readonly
          :value="stats.referral_link"
          class="bg-transparent text-xs text-slate-300 w-full focus:outline-none select-all font-mono"
        />
        <button
          @click="copyLink"
          class="shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white/10 hover:bg-white/20 text-white transition active:scale-95 flex items-center gap-1"
        >
          <span>{{ copied ? '✅' : '📋' }}</span>
          <span>{{ copied ? 'Nusxalandi' : 'Nusxa' }}</span>
        </button>
      </div>

      <!-- Quick Action Buttons -->
      <div class="grid grid-cols-1 gap-2 pt-1">
        <button
          @click="shareOnTelegram"
          class="w-full py-3 px-4 rounded-xl font-bold text-sm bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2 transition active:scale-98"
        >
          <span>🚀</span>
          <span>Telegram orqali do'stlarga yuborish</span>
        </button>
      </div>
    </div>

    <!-- How it works (Infographic) -->
    <div class="rounded-2xl bg-surface border border-white/10 p-5 space-y-4">
      <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Qanday ishlaydi?</h3>

      <div class="space-y-3">
        <div class="flex items-start gap-3">
          <div class="w-7 h-7 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center shrink-0 text-xs border border-amber-500/30">
            1
          </div>
          <div>
            <h4 class="text-sm font-semibold text-white">Havolangizni ulashing</h4>
            <p class="text-xs text-slate-400 mt-0.5">Taklif havolangizni Telegram guruh va do'stlaringizga yuboring.</p>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <div class="w-7 h-7 rounded-full bg-blue-500/20 text-blue-400 font-bold flex items-center justify-center shrink-0 text-xs border border-blue-500/30">
            2
          </div>
          <div>
            <h4 class="text-sm font-semibold text-white">Do'stingiz kiradi</h4>
            <p class="text-xs text-slate-400 mt-0.5">Do'stingiz havola orqali Mini Appga kirishi bilan unga +2 Stars xush kelibsiz bonusi beriladi.</p>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center shrink-0 text-xs border border-emerald-500/30">
            3
          </div>
          <div>
            <h4 class="text-sm font-semibold text-white">Sizga +5 Stars</h4>
            <p class="text-xs text-slate-400 mt-0.5">Har bir yangi do'st uchun hisobingizga avtomatik tarzda +5 Stars taqdim etiladi!</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Claim Code Manually (Optional for users who received a code) -->
    <div class="rounded-2xl bg-surface/60 border border-white/5 p-4 space-y-3">
      <h3 class="text-xs font-semibold text-slate-400">Do'stingiz sizga kod bergan bo'lsa:</h3>
      <div class="flex gap-2">
        <input
          v-model="inputCode"
          placeholder="Masalan: ABC12345"
          class="bg-black/40 border border-white/10 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-primary w-full uppercase font-mono"
        />
        <button
          @click="submitCode"
          :disabled="claiming || !inputCode"
          class="px-4 py-2 bg-primary hover:bg-primary-hover disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shrink-0"
        >
          {{ claiming ? '...' : 'Faollashtirish' }}
        </button>
      </div>
      <p v-if="claimMessage" :class="claimSuccess ? 'text-emerald-400' : 'text-rose-400'" class="text-xs">
        {{ claimMessage }}
      </p>
    </div>

    <!-- Recent Referrals List -->
    <div class="space-y-3">
      <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider flex items-center justify-between">
        <span>Taklif qilingan do'stlar</span>
        <span class="text-xs font-normal text-slate-400">{{ stats.recent_referrals.length }} ta</span>
      </h3>

      <div v-if="loading" class="text-center py-6 text-slate-500 text-xs">
        Yuklanmoqda...
      </div>

      <div v-else-if="stats.recent_referrals.length === 0" class="text-center py-8 rounded-2xl bg-surface/30 border border-white/5 p-6">
        <span class="text-4xl">👥</span>
        <p class="text-sm font-semibold text-slate-300 mt-2">Hali do'stlar qo'shilmadi</p>
        <p class="text-xs text-slate-500 mt-1">Birinchi havolangizni ulashing va Stars yig'ishni boshlang!</p>
      </div>

      <div v-else class="space-y-2">
        <div
          v-for="item in stats.recent_referrals"
          :key="item.id"
          class="flex items-center justify-between p-3 rounded-xl bg-surface border border-white/5"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-xs font-bold text-white">
              {{ item.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="text-xs font-bold text-white">{{ item.name }}</p>
              <p class="text-[10px] text-slate-400">{{ item.username ? '@' + item.username : item.date }}</p>
            </div>
          </div>
          <div class="text-right">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20">
              +{{ item.reward_stars }} ⭐️
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getReferralStats, claimReferral } from '../api/referral'

const loading = ref(true)
const copied = ref(false)
const inputCode = ref('')
const claiming = ref(false)
const claimMessage = ref('')
const claimSuccess = ref(false)

const stats = ref({
  referral_code: '',
  referral_link: '',
  bot_link: '',
  referrals_count: 0,
  bonus_stars: 0,
  recent_referrals: [],
})

const fetchStats = async () => {
  try {
    loading.value = true
    const data = await getReferralStats()
    stats.value = data
  } catch (err) {
    console.error('Error fetching referral stats:', err)
  } finally {
    loading.value = false
  }
}

const copyLink = async () => {
  if (!stats.value.referral_link) return
  try {
    await navigator.clipboard.writeText(stats.value.referral_link)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2500)
  } catch (e) {
    console.error('Clipboard copy failed:', e)
  }
}

const shareOnTelegram = () => {
  const shareText = encodeURIComponent(
    "🍿 AI Kino 2.0 — Sun'iy intellekt tomonidan yaratilgan filmlar va seriallarni birinchi bo'lib tomosha qiling!\n\n🎁 Havola orqali kiring va xush kelibsiz Stars bonusini oling:"
  )
  const shareUrl = encodeURIComponent(stats.value.referral_link)
  const tgUrl = `https://t.me/share/url?url=${shareUrl}&text=${shareText}`

  if (window.Telegram?.WebApp?.openTelegramLink) {
    window.Telegram.WebApp.openTelegramLink(tgUrl)
  } else {
    window.open(tgUrl, '_blank')
  }
}

const submitCode = async () => {
  if (!inputCode.value.trim()) return
  try {
    claiming.value = true
    claimMessage.value = ''
    const res = await claimReferral(inputCode.value.trim())
    claimSuccess.value = true
    claimMessage.value = res.message || 'Muvaffaqiyatli qabul qilindi!'
    inputCode.value = ''
    await fetchStats()
  } catch (err) {
    claimSuccess.value = false
    claimMessage.value = err.response?.data?.message || 'Xatolik yuz berdi'
  } finally {
    claiming.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>
