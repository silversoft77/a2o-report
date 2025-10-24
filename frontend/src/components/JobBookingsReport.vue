<script setup lang="ts">
import { ref, onMounted, watch, onUnmounted } from 'vue'
import axios from 'axios'
import { storeToRefs } from 'pinia'
import { useFilterStore } from '@/stores/filterStore'
import { formatDateCategories, formatISODate } from '@/utils/dateUtils'
import { hexToRgba, BLUE_PALETTE } from '@/utils/colorUtils'
import { createJobBookingsChartOptions } from '@/utils/chartUtils'
import { downloadJobBookingsCSV } from '@/utils/exportUtils'
import { BUTTONS, MESSAGES } from '@/constants/text'
import type { JobBookingsData, JobBookingsSeries } from '@/types/chart'

const filterStore = useFilterStore()
const { selectedMarketIds, fromDate, toDate } = storeToRefs(filterStore)

const chartContainer = ref<HTMLElement | null>(null)
let Highcharts: any = null
let chart: any = null

const loading = ref(false)

const fetchData = async (markets?: string[]): Promise<void> => {
    loading.value = true
    try {
        const params: Record<string, string> = {}
        const fd = formatISODate(fromDate.value)
        const td = formatISODate(toDate.value)
        if (fd) params.fromDate = fd
        if (td) params.toDate = td

        const useMarkets = Array.isArray(markets) && markets.length > 0
            ? markets
            : (Array.isArray(selectedMarketIds.value) ? selectedMarketIds.value : [])

        if (useMarkets && useMarkets.length > 0) {
            params.markets = useMarkets.join(',')
        }

        console.log('Fetching job bookings data with params:', params)
        const resp = await axios.get<JobBookingsData>('/api/reports/job-bookings', { params })
        console.log('Job bookings response:', resp.data)
        renderChart(resp.data)
    } catch (err) {
        console.error(MESSAGES.ERROR_FETCH_JOB_BOOKINGS, err)
    } finally {
        loading.value = false
    }
}

const renderChart = async (data: JobBookingsData): Promise<void> => {
    if (!chartContainer.value) return
    if (!Highcharts) {
        Highcharts = (await import('highcharts')) as any
    }

    const formattedCategories = formatDateCategories(data.categories || [])

    const series: JobBookingsSeries[] = (data.series || []).map((s) => ({ ...s, type: 'areaspline' }))

    const coloredSeries: (JobBookingsSeries & { color?: string; fillColor?: string })[] = series.map((s, idx) => {
        const color = (BLUE_PALETTE[idx % BLUE_PALETTE.length] ?? '#2563EB') as string
        return {
            ...s,
            color,
            fillColor: hexToRgba(color, 0.1)
        }
    })

    const options = createJobBookingsChartOptions(formattedCategories, coloredSeries)

    if (chart) {
        try {
            chart.destroy()
        } catch (e) {
            console.error(MESSAGES.ERROR_DISPATCH_FILTERS_APPLIED, e)
        }
        chart = null
    }
    if (Highcharts && typeof Highcharts.chart === 'function') {
        chart = Highcharts.chart(chartContainer.value, options)
    }
}

const downloadCSV = () => {
    downloadJobBookingsCSV(
        { fromDate: fromDate.value, toDate: toDate.value },
        selectedMarketIds.value
    )
}

const initialized = ref(false)

let _onFiltersApplied: ((e: Event) => void) | null = null

onMounted(() => {
    if (Array.isArray(selectedMarketIds.value) && selectedMarketIds.value.length > 0) {
        fetchData()
        initialized.value = true
    }

    _onFiltersApplied = (e: Event) => {
        const ev = e as CustomEvent | undefined
        const detailMarkets = ev?.detail?.markets as string[] | undefined
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

watch(selectedMarketIds, (val) => {
    if (!initialized.value && Array.isArray(val) && val.length > 0) {
        fetchData()
        initialized.value = true
    }
})
</script>

<template>
    <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
        <div class="flex flex-col mb-4">
            <div class="flex items-center gap-2 justify-end">
                <div class="flex items-center gap-2">
                    <button @click="downloadCSV"
                        class="px-3 py-1.5 bg-gray-700 rounded text-sm text-white hover:bg-gray-600 transition-colors">{{
                            BUTTONS.EXPORT_CSV }}</button>
                </div>
            </div>
            <div class="relative">
                <div ref="chartContainer" v-show="!loading" />
                <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-gray-800/60 rounded">
                    <div class="text-sm text-gray-300">{{ MESSAGES.LOADING }}</div>
                </div>
            </div>
        </div>
        <div v-if="loading" class="text-sm text-gray-400">{{ MESSAGES.LOADING }}</div>
    </div>
</template>
