import { ref, computed } from 'vue'

const savedLang = localStorage.getItem('aikino_lang') || 'uz'
export const currentLang = ref(savedLang)

export const translations = {
  uz: {
    // Nav
    home: 'Asosiy',
    catalog: 'Katalog',
    purchases: 'Xaridlar',
    profile: 'Profil',

    // Home
    watch_now: 'Tomosha qilish',
    continue_watching: 'Davom ettirish',
    last_watched: 'Oxirgi ko‘rilgan',
    episode_num: '{n}-qism',
    trending_ai: '🔥 Tavsiya etilgan AI Asarlar',
    view_all: 'Barchasi',
    free: 'Bepul',
    stars: 'Stars',

    // Catalog
    search_placeholder: 'Film yoki serial qidirish...',
    all: 'Barchasi',
    films: 'Filmlar',
    series: 'Seriallar',
    shorts: 'Qisqa metraj',
    all_genres: 'Barcha janrlar',
    no_results: 'Hech qanday AI kino topilmadi',

    // Work Detail
    back: 'Orqaga',
    buy_full_work: 'To‘liq asarni xarid qilish',
    buy_full_work_sub: 'Barcha qismlarni bir marta oching',
    unlock_with_stars: 'Telegram Stars bilan ochish',
    creating_invoice: 'Hisob yaratilmoqda...',
    episodes: 'Qismlar',
    minutes: 'daqiqa',
    status_free: 'Bepul',
    status_owned: 'Ochilgan',
    status_locked: 'Qulflangan',
    btn_watch: 'Ko‘rish',
    btn_unlock: 'Ochish',
    video_sent_toast: '🎬 Video bot chatiga yuborildi! Telegram chatiga o‘ting.',
    invoice_sent_toast: '⭐ To‘lov hisobi Telegram bot chatiga yuborildi!',

    // Purchases
    my_purchases_title: 'Mening xaridlarim',
    no_purchases: 'Sizda hali pullik xaridlar mavjud emas',
    btn_browse_catalog: 'Katalogni ko‘rish',
    work_purchase: 'To‘liq asar xaridi',
    episode_purchase: 'Epizod xaridi',
    badge_active: 'Faol',

    // Profile
    guest_user: 'Mehmon tomoshabin',
    local_mode: 'Lokal rejim',
    interface_language: 'Interfeys tili',
    lang_uz: '🇺🇿 O‘zbekcha',
    lang_ru: '🇷🇺 Русский',
    platform_rules_title: 'Platforma qoidalari',
    rule_1: '• Barcha kontent 100% sun‘iy intellekt texnologiyalari yordamida yaratilgan.',
    rule_2: '• Yosh toifalari: 0+, 6+, 12+, 16+. 18+ kontent qat‘iyan taqiqlangan.',
    rule_3: '• To‘lovlar Telegram Stars (XTR) orqali rasmiy Telegram to‘lov tizimida amalga oshiriladi.',
    app_version: 'AI Kino 2.0 • Versiya: 2.0.0 (MVP)',
    app_subtitle: 'Telegram Mini App + Bot Integration',
  },
  ru: {
    // Nav
    home: 'Главная',
    catalog: 'Каталог',
    purchases: 'Покупки',
    profile: 'Профиль',

    // Home
    watch_now: 'Смотреть сейчас',
    continue_watching: 'Продолжить просмотр',
    last_watched: 'Последняя серия',
    episode_num: 'Серия {n}',
    trending_ai: '🔥 Рекомендуемые ИИ-фильмы',
    view_all: 'Все',
    free: 'Бесплатно',
    stars: 'Stars',

    // Catalog
    search_placeholder: 'Поиск фильма или сериала...',
    all: 'Все',
    films: 'Фильмы',
    series: 'Сериалы',
    shorts: 'Короткометражки',
    all_genres: 'Все жанры',
    no_results: 'Ничего не найдено',

    // Work Detail
    back: 'Назад',
    buy_full_work: 'Купить весь фильм / сериал',
    buy_full_work_sub: 'Откройте все серии разом',
    unlock_with_stars: 'Оплатить через Telegram Stars',
    creating_invoice: 'Создание счета...',
    episodes: 'Серии',
    minutes: 'мин',
    status_free: 'Бесплатно',
    status_owned: 'Куплено',
    status_locked: 'Заблокировано',
    btn_watch: 'Смотреть',
    btn_unlock: 'Открыть',
    video_sent_toast: '🎬 Видео отправлено в чат бота! Откройте диалог.',
    invoice_sent_toast: '⭐ Счет для оплаты отправлен в чат Telegram бота!',

    // Purchases
    my_purchases_title: 'Мои покупки',
    no_purchases: 'У вас пока нет платных покупок',
    btn_browse_catalog: 'Перейти в каталог',
    work_purchase: 'Покупка всего произведения',
    episode_purchase: 'Покупка серии',
    badge_active: 'Активно',

    // Profile
    guest_user: 'Гостевой зритель',
    local_mode: 'Локальный режим',
    interface_language: 'Язык интерфейса',
    lang_uz: '🇺🇿 O‘zbekcha',
    lang_ru: '🇷🇺 Русский',
    platform_rules_title: 'Правила платформы',
    rule_1: '• Весь контент создан на 100% с помощью технологий искусственного интеллекта.',
    rule_2: '• Возрастные категории: 0+, 6+, 12+, 16+. Контент 18+ строго запрещен.',
    rule_3: '• Оплата осуществляется в Telegram Stars (XTR) через официальную платежную систему Telegram.',
    app_version: 'AI Kino 2.0 • Версия: 2.0.0 (MVP)',
    app_subtitle: 'Telegram Mini App + Bot Integration',
  }
}

export function t(key, params = {}) {
  let text = translations[currentLang.value]?.[key] || translations['uz']?.[key] || key
  for (const [k, v] of Object.entries(params)) {
    text = text.replace(`{${k}}`, v)
  }
  return text
}

export function setLanguage(lang) {
  if (['uz', 'ru'].includes(lang)) {
    currentLang.value = lang
    localStorage.setItem('aikino_lang', lang)
  }
}

export function toggleLanguage() {
  setLanguage(currentLang.value === 'uz' ? 'ru' : 'uz')
}
