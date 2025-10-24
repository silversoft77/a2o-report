import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import ReportsView from '../views/ReportsView.vue'
import { API_CONFIG } from '@/config/api'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            redirect: '/reports'
        },
        {
            path: '/login',
            name: 'login',
            component: LoginView
        },
        {
            path: '/reports',
            name: 'reports',
            component: ReportsView
        }
    ]
})

router.beforeEach(async (to) => {
    if (to.path === '/login') {
        return true
    }

    try {
        await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.USER}`, {
            credentials: 'include'
        })
        return true
    } catch (error) {
        return '/login'
    }
})

export default router