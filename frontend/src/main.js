
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'
import { createPinia } from 'pinia'
import { VueQueryPlugin } from "@tanstack/vue-query";

import '@coreui/coreui/dist/css/coreui.min.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import './style.css'

const app = createApp(App)

app.use(router)
app.use(createPinia())
app.use(VueQueryPlugin)
app.mount('#app')
