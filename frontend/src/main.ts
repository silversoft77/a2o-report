import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'
import { API_CONFIG } from '@/config/api'
import App from './App.vue'
import router from './router'
import './style.css'
import './styles/multiselect.css'

axios.defaults.withCredentials = true
axios.defaults.baseURL = API_CONFIG.BASE_URL
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

axios.interceptors.request.use(function (config) {
    const token = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]

    if (token) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token)
    }
    return config
})

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.use(router)
app.mount('#app')
