const getAuthHeaders = () => {
  const token = localStorage.getItem('aikino_token')
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
  }
}

export const getReferralStats = async () => {
  const res = await fetch('/api/v1/referrals/stats', {
    headers: getAuthHeaders(),
  })
  if (!res.ok) {
    throw new Error('Referal ma\'lumotlarini yuklab bo\'lmadi')
  }
  const json = await res.json()
  return json.data
}

export const claimReferral = async (code) => {
  const res = await fetch('/api/v1/referrals/claim', {
    method: 'POST',
    headers: getAuthHeaders(),
    body: JSON.stringify({ code }),
  })
  const json = await res.json()
  if (!res.ok) {
    const error = new Error(json.message || 'Xatolik yuz berdi')
    error.response = { data: json }
    throw error
  }
  return json
}
