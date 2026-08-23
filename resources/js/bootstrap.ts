import axios from 'axios'

window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
window.axios.defaults.baseURL = '/api/v1'

// Adjuntar automáticamente el token en cada petición
const token = localStorage.getItem('auth_token')
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// Interceptor para redirigir al login si el token expira (401)
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token')
            if (window.location.pathname !== '/login') {
                window.location.href = '/login'
            }
        }
        return Promise.reject(error)
    }
)