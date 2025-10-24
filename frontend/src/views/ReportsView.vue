<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import DashboardLayout from '../components/DashboardLayout.vue'
import JobBookingsReport from '@/components/JobBookingsReport.vue'
import ConversionFunnel from '@/components/ConversionFunnel.vue'
import { TITLES } from '@/constants/text'

const router = useRouter()

onMounted(async () => {
    try {
        await axios.get('/api/user')
    } catch (error) {
        router.push('/login')
    }
})
</script>

<template>
    <DashboardLayout>
        <div class="space-y-6">
            <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6">
                <h2 class="text-xl font-bold text-white mb-4">{{ TITLES.REPORTS_OVERVIEW }}</h2>
                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4">
                        <JobBookingsReport />
                        <ConversionFunnel />
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>