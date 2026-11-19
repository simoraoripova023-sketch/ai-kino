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
    btn_retry: 'Qayta urinish',

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
    in_app_player: 'Ilova ichida ko‘rish',
    open_in_bot: 'Telegram botda ochish',
    close_player: 'Yopish',
    simulate_pay_btn: '⚡ Sinov to‘lovi (Sandbox)',
    payment_success_toast: '🎉 To‘lov muvaffaqiyatli qabul qilindi! Kontent ochildi.',
    watch_movie: 'Filmga o‘tish',

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

    // Admin & Sandbox
    admin_panel_title: 'Admin Paneli',
    admin_panel_sub: 'Kontent, statistika va to‘lovlar nazorati',
    btn_open: 'Ochish',
    btn_refresh: 'Yangilash',
    btn_add_work: 'Asar qo‘shish',
    btn_hide: 'Yashirish',
    btn_publish: 'Nashr qilish',
    btn_save: 'Saqlash',
    btn_cancel: 'Bekor qilish',
    stat_total_users: 'Foydalanuvchilar',
    stat_revenue_stars: 'Stars Tushumi',
    stat_works: 'Asarlar',
    stat_delivered: 'Videolar',
    tab_works: 'Asarlar',
    tab_payments: 'To‘lovlar',
    tab_audit: 'Audit loglar',
    all_works_list: 'Barcha asarlar',
    recent_payments: 'Oxirgi to‘lovlar',
    audit_logs_title: 'Administrator harakatlari',
    no_payments_yet: 'Hali to‘lovlar mavjud emas',
    no_logs: 'Hali loglar yo‘q',
    create_work_title: 'Yangi AI asar qo‘shish',
    field_title: 'Asar nomi',
    field_format: 'Format',
    field_style: 'Stil',
    field_pricing: 'Narxlash turi',
    field_stars: 'Narx (Stars)',
    field_poster_url: 'Poster rasmi URL',
    field_description: 'Tavsif',
    status_published: 'Nashr qilingan',
    status_hidden: 'Yashirilgan',
    transactions: 'tranzaksiya',
    sandbox_mode_title: 'Sandbox test rejimi',
    sandbox_mode_desc: 'Brauzerda sinash uchun rolni tanlang:',
    role_viewer: 'Oddiy Tomoshabin',
    role_admin: 'Bosh Administrator',
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
    btn_retry: 'Повторить',

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
    in_app_player: 'Смотреть в плеере',
    open_in_bot: 'Открыть в боте Telegram',
    close_player: 'Закрыть',
    simulate_pay_btn: '⚡ Тестовая оплата (Sandbox)',
    payment_success_toast: '🎉 Оплата прошла успешно! Контент открыт.',
    watch_movie: 'Перейти к фильму',

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

    // Admin & Sandbox
    admin_panel_title: 'Панель администратора',
    admin_panel_sub: 'Контент, статистика и управление Stars',
    btn_open: 'Открыть',
    btn_refresh: 'Обновить',
    btn_add_work: 'Добавить фильм',
    btn_hide: 'Скрыть',
    btn_publish: 'Опубликовать',
    btn_save: 'Сохранить',
    btn_cancel: 'Отмена',
    stat_total_users: 'Пользователи',
    stat_revenue_stars: 'Выручка Stars',
    stat_works: 'Произведения',
    stat_delivered: 'Видео',
    tab_works: 'Контент',
    tab_payments: 'Платежи',
    tab_audit: 'Аудит-логи',
    all_works_list: 'Все произведения',
    recent_payments: 'Последние платежи',
    audit_logs_title: 'Действия администратора',
    no_payments_yet: 'Платежей пока нет',
    no_logs: 'Логов пока нет',
    create_work_title: 'Добавить ИИ-произведение',
    field_title: 'Название',
    field_format: 'Формат',
    field_style: 'Стиль',
    field_pricing: 'Тип оплаты',
    field_stars: 'Цена (Stars)',
    field_poster_url: 'URL постера',
    field_description: 'Описание',
    status_published: 'Опубликовано',
    status_hidden: 'Скрыто',
    transactions: 'транзакций',
    sandbox_mode_title: 'Тестовый режим Sandbox',
    sandbox_mode_desc: 'Выберите роль для тестирования в браузере:',
    role_viewer: 'Обычный зритель',
    role_admin: 'Главный Администратор',
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
