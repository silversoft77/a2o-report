import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { API_CONFIG } from '@/config/api'
import type { Market } from './type'

export const useFilterStore = defineStore('filter', () => {
    const selectedMarketIds = ref<string[]>([])
    const availableMarkets = ref<Market[]>([])
    const appliedKey = ref<number>(0)

    const selectedMarkets = computed(() =>
        selectedMarketIds.value.map(id =>
            availableMarkets.value.find(m => m.id === id)
        ).filter((m): m is Market => m !== undefined)
    )

    const today = new Date()
    const fromDate = ref<Date>(new Date(2024, today.getMonth() - 1, today.getDate()))
    const toDate = ref<Date>(new Date(2024, today.getMonth(), today.getDate()))
    const isLoading = ref(false)

    const fetchUserMarkets = async (userId?: string) => {
        try {
            const url = userId
                ? `${API_CONFIG.ENDPOINTS.USER_MARKETS}?user_id=${encodeURIComponent(userId)}`
                : `${API_CONFIG.ENDPOINTS.USER_MARKETS}`

            const response = await axios.get(url)
            availableMarkets.value = response.data || []

            if (availableMarkets.value.length > 0) {
                selectedMarketIds.value = [availableMarkets.value[0]!.id]
            } else {
                selectedMarketIds.value = []
            }
        } catch (error) {
            console.error('Error fetching markets:', error)
        }
    }

    const applyFilters = async () => {
        isLoading.value = true
        try {
            const filters = {
                markets: selectedMarketIds.value.join(','),
                fromDate: fromDate.value.toISOString().split('T')[0],
                toDate: toDate.value.toISOString().split('T')[0]
            }

            const response = await axios.post(`${API_CONFIG.ENDPOINTS.APPLY_FILTERS}`, filters)
            appliedKey.value++
            return response.data
        } catch (error) {
            console.error('Error applying filters:', error)
            throw error
        } finally {
            isLoading.value = false
        }
    }

    return {
        selectedMarketIds,
        selectedMarkets,
        availableMarkets,
        appliedKey,
        fromDate,
        toDate,
        isLoading,
        fetchUserMarkets,
        applyFilters
    }
})