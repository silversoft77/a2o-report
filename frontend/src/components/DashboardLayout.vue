<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { HEADERS, BUTTONS } from '../constants/text'

const router = useRouter()
const user = ref({ name: '' })

onMounted(async () => {
    try {
        const response = await axios.get('/api/user')
        user.value = response.data
    } catch (error) {
        console.error('Error fetching user:', error)
        router.push('/login')
    }
})

const signOut = async () => {
    try {
        await axios.post('/api/logout')
        localStorage.removeItem('auth_token')
        delete axios.defaults.headers.common['Authorization']
        router.push('/login')
    } catch (error) {
        console.error('Logout error:', error)
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-900">
        <nav class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="text-xl font-bold text-white">
                            {{ HEADERS.REPORTS_DASHBOARD }}
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-300">{{ user.name }}</span>
                        <button @click="signOut"
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white bg-gray-700 hover:bg-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white transition-colors">
                            {{ BUTTONS.SIGN_OUT }}
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <slot></slot>
        </main>
    </div>
</template>