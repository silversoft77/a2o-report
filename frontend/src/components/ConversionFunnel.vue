<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { storeToRefs } from 'pinia'
import { useFilterStore } from '@/stores/filterStore'
import { formatISODate } from '@/utils/dateUtils'
import { downloadReportCSVAsync } from '@/utils/exportUtils'
import { TITLES, BUTTONS, MESSAGES } from '@/constants/text'

const filterStore = useFilterStore()
const { selectedMarketIds, fromDate, toDate } = storeToRefs(filterStore)

const stepColors = ['#99CCCC', '#006666', '#009999', '#00CCCC', '#0097b9', '#0099CC'];

const loading = ref(false)
const error = ref<string | null>(null)
const steps = ref<string[]>([])
const combined = ref<number[]>([])
const combinedPercent = ref<number[]>([])
const markets = ref<Array<{ id: string; name: string; counts: number[] }>>([])

const fetchData = async (marketsArg?: string[]) => {
    loading.value = true
    error.value = null

    try {
        const params: Record<string, string> = {}
        const fd = formatISODate(fromDate.value)
        const td = formatISODate(toDate.value)
        if (fd) params.fromDate = fd
        if (td) params.toDate = td
        const useMarkets = Array.isArray(marketsArg) && marketsArg.length > 0
            ? marketsArg
            : (Array.isArray(selectedMarketIds.value) ? selectedMarketIds.value : [])
        if (useMarkets && useMarkets.length > 0) params.markets = useMarkets.join(',')

        const resp = await axios.get('/api/reports/conversion-funnel', { params })
        if (resp.data) {
            steps.value = resp.data.steps || []
            markets.value = resp.data.markets || []
            combined.value = resp.data.combined || []
            combinedPercent.value = resp.data.combined_percentages || []
        } else {
            error.value = MESSAGES.CONVERSION_NO_DATA_RECEIVED
        }
    } catch (err) {
        console.error(MESSAGES.ERROR_FETCH_CONVERSION_FUNNEL, err)
        error.value = MESSAGES.CONVERSION_LOAD_FAILED
    } finally {
        loading.value = false
    }
}

let _onFiltersApplied: ((e: any) => void) | null = null

onMounted(() => {
    fetchData()

    _onFiltersApplied = (e: any) => {
        const detailMarkets: string[] | undefined = e?.detail?.markets

        if (detailMarkets && Array.isArray(detailMarkets)) {
            fetchData(detailMarkets)
        } else {
            fetchData()
        }
    }
    window.addEventListener('filters:applied', _onFiltersApplied)
})

onUnmounted(() => {
    if (_onFiltersApplied) {
        window.removeEventListener('filters:applied', _onFiltersApplied)
        _onFiltersApplied = null
    }
})

const downloadCSV = async () => {
    const params: Record<string, any> = {}
    const fd = formatISODate(fromDate.value)
    const td = formatISODate(toDate.value)

    if (fd) params.fromDate = fd
    if (td) params.toDate = td
    if (Array.isArray(selectedMarketIds.value) && selectedMarketIds.value.length > 0) {
        params.markets = selectedMarketIds.value.join(',')
    }
    await downloadReportCSVAsync('/api/reports/conversion-funnel', params)
}
</script>

<template>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg text-white font-semibold">{{ TITLES.REPORTS_OVERVIEW }} - {{ TITLES.CONVERSION_FUNNEL }}
            </h3>
            <div>
                <button @click="downloadCSV"
                    class="px-3 py-1.5 bg-gray-700 rounded text-sm text-white hover:bg-gray-600 transition-colors">{{
                        BUTTONS.EXPORT_CSV }}</button>
            </div>
        </div>
        <div v-if="loading" class="text-sm text-gray-400">{{ MESSAGES.LOADING }}</div>
        <div v-else-if="error" class="text-sm text-red-400">{{ error }}</div>
        <div v-else-if="steps.length === 0" class="text-sm text-gray-400">{{ MESSAGES.NO_CONVERSION_DATA_AVAILABLE }}
        </div>
        <div v-else>
            <div class="space-y-4">
                <div v-for="(step, idx) in steps" :key="step" class="flex items-center gap-4">
                    <div class="w-72 text-sm text-gray-200 flex items-center">
                        <span>{{ step }}</span>
                        <span class="ml-2 text-gray-400">({{ combined[idx] ?? 0 }})</span>
                    </div>
                    <div class="flex-1 bg-gray-100 h-6 rounded-3xl overflow-hidden">
                        <div class="h-6 rounded-2xl text-xs text-white flex items-center justify-center transition-all duration-300"
                            :style="{
                                width: (combinedPercent[idx] || 0) + '%',
                                backgroundColor: stepColors[idx] || stepColors[0]
                            }">
                            <span class="px-2 font-medium">{{ (combinedPercent[idx] !== undefined) ?
                                (combinedPercent[idx].toFixed(2) + '%') : '0%' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
