<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { TITLES, LABELS, PLACEHOLDERS, BUTTONS, MESSAGES } from '@/constants/text'
import { API_CONFIG } from '@/config/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

const login = async () => {
    try {
        await axios.get(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.CSRF}`)
        await new Promise(resolve => setTimeout(resolve, 100))

        const response = await axios.post(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.LOGIN}`, {
            email: email.value,
            password: password.value
        })

        if (response.data.token) {
            localStorage.setItem('auth_token', response.data.token)
            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
        }

        router.push('/reports')
    } catch (e: any) {
        console.error('Login error:', e.response?.data || e)
        error.value = e.response?.data?.message || MESSAGES.LOGIN_ERROR
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-900">
        <div class="max-w-md w-full space-y-8 p-8 bg-gray-800 rounded-lg shadow-lg border border-gray-700">
            <div>
                <h2 class="mt-6 text-center text-3xl font-semibold text-white">{{ TITLES.SIGN_IN }}</h2>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="login">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300">{{ LABELS.EMAIL }}</label>
                        <input id="email" name="email" type="email" required v-model="email"
                            class="mt-1 appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-600 bg-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm"
                            :placeholder="PLACEHOLDERS.EMAIL" />
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300">{{
                            LABELS.PASSWORD }}</label>
                        <input id="password" name="password" type="password" required v-model="password"
                            class="mt-1 appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-600 bg-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm"
                            :placeholder="PLACEHOLDERS.PASSWORD" />
                    </div>
                </div>

                <div v-if="error" class="text-red-500 text-sm text-center">{{ error }}</div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-cyan-500 transition-colors">
                        {{ BUTTONS.SIGN_IN }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>