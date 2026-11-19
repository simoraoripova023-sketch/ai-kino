import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useCatalogStore = defineStore('catalog', () => {
  const works = ref([])
  const homeWorks = ref([])
  const currentWork = ref(null)
  const episodes = ref([])
  const genres = ref([])
  const progressList = ref([])
  const isLoading = ref(false)
  const isHomeLoading = ref(false)
  const error = ref(null)
  const homeError = ref(null)

  // Filters
  const selectedFormat = ref('')
  const selectedStyle = ref('')
  const selectedGenre = ref('')
  const selectedAgeRating = ref('')
  const selectedPricingMode = ref('')
  const selectedSort = ref('newest')
  const searchQuery = ref('')

  async function fetchGenres() {
    try {
      const res = await fetch('/api/v1/genres')
      if (res.ok) {
        const json = await res.json()
        genres.value = json.data || []
      }
    } catch (err) {
      console.error('Fetch genres error:', err)
    }
  }

  async function fetchHomeWorks() {
    isHomeLoading.value = true
    homeError.value = null
    try {
      const res = await fetch('/api/v1/works?per_page=12')
      if (res.ok) {
        const json = await res.json()
        homeWorks.value = json.data || []
      } else {
        throw new Error('Tavsiya etilgan asarlarni yuklab bo‘lmadi')
      }
    } catch (err) {
      homeError.value = err.message
      console.error('Fetch home works error:', err)
    } finally {
      isHomeLoading.value = false
    }
  }

  async function fetchWorks() {
    isLoading.value = true
    error.value = null
    try {
      const params = new URLSearchParams()
      if (selectedFormat.value) params.append('format', selectedFormat.value)
      if (selectedStyle.value) params.append('style', selectedStyle.value)
      if (selectedGenre.value) params.append('genre', selectedGenre.value)
      if (selectedAgeRating.value) params.append('age_rating', selectedAgeRating.value)
      if (selectedPricingMode.value) params.append('pricing_mode', selectedPricingMode.value)
      if (selectedSort.value) params.append('sort', selectedSort.value)
      if (searchQuery.value) params.append('q', searchQuery.value)

      const res = await fetch(`/api/v1/works?${params.toString()}`)
      if (res.ok) {
        const json = await res.json()
        works.value = json.data || []
      } else {
        throw new Error('Asarlarni yuklab bo‘lmadi')
      }
    } catch (err) {
      error.value = err.message
      console.error('Fetch works error:', err)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchWorkDetail(id) {
    isLoading.value = true
    error.value = null
    try {
      const token = localStorage.getItem('aikino_token')
      const headers = token ? { Authorization: `Bearer ${token}` } : {}

      const res = await fetch(`/api/v1/works/${id}`, { headers })
      if (res.ok) {
        const json = await res.json()
        currentWork.value = json.data.work
        episodes.value = json.data.episodes || []
      } else {
        throw new Error('Asar tafsilotlarini yuklab bo‘lmadi')
      }
    } catch (err) {
      error.value = err.message
      console.error('Fetch work detail error:', err)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchProgress() {
    const token = localStorage.getItem('aikino_token')
    if (!token) return

    try {
      const res = await fetch('/api/v1/progress', {
        headers: { Authorization: `Bearer ${token}` },
      })
      if (res.ok) {
        const json = await res.json()
        progressList.value = json.data || []
      }
    } catch (err) {
      console.error('Fetch progress error:', err)
    }
  }

  return {
    works,
    homeWorks,
    currentWork,
    episodes,
    genres,
    progressList,
    isLoading,
    isHomeLoading,
    error,
    homeError,
    selectedFormat,
    selectedStyle,
    selectedGenre,
    selectedAgeRating,
    selectedPricingMode,
    selectedSort,
    searchQuery,
    fetchGenres,
    fetchHomeWorks,
    fetchWorks,
    fetchWorkDetail,
    fetchProgress,
  }
})
