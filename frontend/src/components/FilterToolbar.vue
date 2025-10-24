<script setup lang="ts">
import { onMounted, computed } from 'vue'
import axios from 'axios'
import { storeToRefs } from 'pinia'
import Datepicker from '@vuepic/vue-datepicker'
import Multiselect from '@vueform/multiselect'
import { useFilterStore } from '@/stores/filterStore'
import { API_CONFIG } from '@/config/api'
import { UI_LABELS, BUTTONS, PLACEHOLDERS } from '@/constants/text'

const filterStore = useFilterStore()
const { selectedMarketIds, availableMarkets, fromDate, toDate, isLoading } = storeToRefs(filterStore)

const selectedMarketNames = computed(() =>
    selectedMarketIds.value
        ?.map(id => availableMarkets.value.find(m => m.id === id)?.name)
        .filter(Boolean) || []
)

const toggleMarket = (id: string | undefined) => {
    if (!id) return
    if (!Array.isArray(selectedMarketIds.value)) {
        selectedMarketIds.value = [id]
        return
    }

    const idx = selectedMarketIds.value.indexOf(id)
    if (idx === -1) {
        selectedMarketIds.value = [...selectedMarketIds.value, id]
    } else {
        selectedMarketIds.value = selectedMarketIds.value.filter(i => i !== id)
    }
}

const handleApply = async () => {
    try {
        await filterStore.applyFilters()
    } catch (error) {
    }
}

onMounted(async () => {
    try {
        const userResp = await axios.get(API_CONFIG.ENDPOINTS.USER)
        const userId = userResp.data?.id
        await filterStore.fetchUserMarkets(userId)
    } catch (err) {
        await filterStore.fetchUserMarkets()
    }
})

</script>

<template>
    <div class="p-4 bg-gray-800 border-b border-gray-700 flex justify-center">
        <div class="flex flex-wrap gap-4 items-end w-[1280px] justify-between">
            <div class="flex gap-4">
                <div class="flex flex-col">
                    <div class="flex justify-between items-baseline">
                        <label class="block text-sm font-medium text-gray-300 mb-1">{{ UI_LABELS.MARKETS }}</label>
                        <span class="text-sm text-gray-400">{{ selectedMarketNames.length }} selected</span>
                    </div>
                    <Multiselect v-model="selectedMarketIds" :options="availableMarkets" :multiple="true" searchable
                        mode="tags" valueProp="id" label="name" track-by="id" :placeholder="PLACEHOLDERS.SELECT_MARKETS"
                        class="multiselect-dark w-[600px] bg-transparent border-1 border-white/30"
                        :closeOnSelect="false">
                        <template #tag="{ option, handleTagRemove, disabled }">
                            <div class="multiselect-tag is-user bg-transparent">
                                <span>
                                    {{typeof option === 'object' ? option.name : (availableMarkets.find(m => m.id ===
                                        option)?.name || '')}}
                                </span>
                                <i v-if="!disabled" @click.prevent="handleTagRemove(option, $event)"
                                    class="multiselect-tag-remove"></i>
                            </div>
                        </template>
                        <template #option="{ option }">
                            <div class="flex items-center gap-2 text-white">
                                <input type="checkbox"
                                    :checked="Array.isArray(selectedMarketIds) && selectedMarketIds.includes(option.id)"
                                    @click.stop.prevent="toggleMarket(option.id)" class="form-checkbox h-4 w-4" />
                                {{ option.name }}
                            </div>
                        </template>
                    </Multiselect>
                </div>

                <div class="flex flex-col h-16 justify-between">
                    <label class="block text-sm font-medium text-gray-300 mb-1">{{ UI_LABELS.FROM_DATE }}</label>
                    <Datepicker v-model="fromDate" :enable-time-picker="false" dark :min-date="new Date(2024, 0, 1)"
                        :max-date="toDate" :year-range="[2024, 2024]" auto-apply :clearable="false"
                        :placeholder="PLACEHOLDERS.START_DATE" class="datepicker-dark w-[160px]" />
                </div>

                <div class="flex flex-col h-16 justify-between">
                    <label class="block text-sm font-medium text-gray-300 mb-1">{{ UI_LABELS.TO_DATE }}</label>
                    <Datepicker v-model="toDate" :enable-time-picker="false" dark :min-date="fromDate"
                        :max-date="new Date(2024, 11, 31)" :year-range="[2024, 2024]" auto-apply :clearable="false"
                        :placeholder="PLACEHOLDERS.END_DATE" class="datepicker-dark w-[160px]" />
                </div>
            </div>

            <div class="flex-none">
                <button @click="handleApply" :disabled="isLoading"
                    class="px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed h-[38px] flex items-center justify-center">
                    {{ isLoading ? BUTTONS.APPLYING : BUTTONS.APPLY_FILTERS }}
                </button>
            </div>
        </div>
    </div>
</template>
