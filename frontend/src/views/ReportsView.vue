<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import DashboardLayout from '../components/DashboardLayout.vue'

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
                <h2 class="text-xl font-bold text-white mb-4">Reports Overview</h2>
                <div class="grid gap-4">
                    <div class="bg-gray-700 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-200 mb-2">Recent Reports</h3>
                        <p class="text-gray-400">Your reports and analytics will be displayed here.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>