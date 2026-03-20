import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router/index.js'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import './assets/css/theme.css'
import './assets/css/index.css'   // ← si tu l'as copié

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

// Composants UI globaux
import Accordion       from '@/components/ui/Accordion.vue'
import Alert           from '@/components/ui/Alert.vue'
import AlertDialog     from '@/components/ui/AlertDialog.vue'
import AspectRatio     from '@/components/ui/AspectRatio.vue'
import Avatar          from '@/components/ui/Avatar.vue'
import Badge           from '@/components/ui/Badge.vue'
import Breadcrumb      from '@/components/ui/Breadcrumb.vue'
import Button          from '@/components/ui/Button.vue'
import Calendar        from '@/components/ui/Calendar.vue'
import Card            from '@/components/ui/Card.vue'
import Carousel        from '@/components/ui/Carousel.vue'
import Chart           from '@/components/ui/Chart.vue'
import Checkbox        from '@/components/ui/Checkbox.vue'
import Collapsible     from '@/components/ui/Collapsible.vue'
import Command         from '@/components/ui/Command.vue'
import ContextMenu     from '@/components/ui/ContextMenu.vue'
import Dialog          from '@/components/ui/Dialog.vue'
import DropdownMenu    from '@/components/ui/DropdownMenu.vue'
import BottomNav       from '@/components/ui/BottomNav.vue'
import Layout          from '@/components/ui/Layout.vue'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(Toast, { position: 'bottom-right', timeout: 4000 })

app.component('Accordion',    Accordion)
app.component('Alert',        Alert)
app.component('AlertDialog',  AlertDialog)
app.component('AspectRatio',  AspectRatio)
app.component('Avatar',       Avatar)
app.component('Badge',        Badge)
app.component('Breadcrumb',   Breadcrumb)
app.component('Button',       Button)
app.component('Calendar',     Calendar)
app.component('Card',         Card)
app.component('Carousel',     Carousel)
app.component('Chart',        Chart)
app.component('Checkbox',     Checkbox)
app.component('Collapsible',  Collapsible)
app.component('Command',      Command)
app.component('ContextMenu',  ContextMenu)
app.component('Dialog',       Dialog)
app.component('DropdownMenu', DropdownMenu)
app.component('BottomNav',    BottomNav)
app.component('Layout',       Layout)

app.mount('#app')