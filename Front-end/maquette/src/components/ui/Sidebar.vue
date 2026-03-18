<template>
  <div class="d-flex min-vh-100 w-100">

    <!-- ═══ SIDEBAR DESKTOP ═══ -->
    <nav
      v-if="!isMobile"
      class="d-none d-md-flex flex-column bg-white border-end"
      :style="{ width: isOpen ? '16rem' : (collapsible === 'icon' ? '3rem' : '0'), transition: 'width 0.2s ease', overflow: 'hidden', flexShrink: 0 }"
    >
      <!-- Header -->
      <div v-if="$slots.header" class="p-2 border-bottom">
        <slot name="header" />
      </div>

      <!-- Toggle trigger -->
      <div class="px-2 pt-2 d-flex" :class="isOpen ? 'justify-content-end' : 'justify-content-center'">
        <button class="btn btn-light btn-sm p-1" @click="toggleSidebar" title="Toggle Sidebar">
          <PanelLeft style="width:16px;height:16px" />
        </button>
      </div>

      <!-- Menu items -->
      <ul class="list-unstyled flex-grow-1 overflow-auto px-2 py-2 mb-0 d-flex flex-column gap-1">
        <li v-for="item in items" :key="item.label" class="position-relative">

          <!-- Item avec sous-menu -->
          <div v-if="item.children">
            <button
              class="sidebar-btn d-flex align-items-center gap-2 w-100 px-2 py-1 rounded border-0 bg-transparent text-dark"
              :class="{ 'bg-light fw-medium': isItemActive(item) }"
              @click="toggleGroup(item.label)"
            >
              <component v-if="item.icon" :is="item.icon" style="width:16px;height:16px;flex-shrink:0" />
              <span v-if="isOpen" class="flex-grow-1 text-start text-truncate">{{ item.label }}</span>
              <ChevronDown v-if="isOpen" style="width:14px;height:14px" :class="{ 'rotate-180': openGroups.includes(item.label) }" />
            </button>
            <ul v-if="isOpen && openGroups.includes(item.label)" class="list-unstyled ps-3 border-start ms-3 mt-1 d-flex flex-column gap-1">
              <li v-for="child in item.children" :key="child.label">
                <router-link
                  :to="child.to"
                  class="sidebar-btn d-flex align-items-center gap-2 px-2 py-1 rounded text-decoration-none text-dark small"
                  :class="{ 'bg-light fw-medium': $route.path === child.to }"
                >
                  <component v-if="child.icon" :is="child.icon" style="width:14px;height:14px" />
                  <span class="text-truncate">{{ child.label }}</span>
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Item simple -->
          <router-link
            v-else
            :to="item.to"
            class="sidebar-btn d-flex align-items-center gap-2 px-2 py-1 rounded text-decoration-none text-dark"
            :class="{ 'bg-light fw-medium': $route.path === item.to }"
          >
            <component v-if="item.icon" :is="item.icon" style="width:16px;height:16px;flex-shrink:0" />
            <span v-if="isOpen" class="text-truncate flex-grow-1">{{ item.label }}</span>
            <span v-if="isOpen && item.badge" class="badge bg-secondary ms-auto">{{ item.badge }}</span>
          </router-link>

          <!-- Tooltip si collapsed -->
          <div v-if="!isOpen && item.label" class="sidebar-tooltip">{{ item.label }}</div>
        </li>
      </ul>

      <!-- Footer -->
      <div v-if="$slots.footer" class="p-2 border-top">
        <slot name="footer" />
      </div>
    </nav>

    <!-- ═══ SIDEBAR MOBILE (offcanvas) ═══ -->
    <Teleport to="body">
      <Transition name="slide-left">
        <nav
          v-if="isMobile && openMobile"
          class="offcanvas offcanvas-start show d-flex flex-column bg-white"
          style="width:18rem; visibility:visible"
        >
          <div class="offcanvas-header border-bottom">
            <span class="fw-semibold">Menu</span>
            <button class="btn-close ms-auto" @click="openMobile = false" />
          </div>
          <ul class="list-unstyled flex-grow-1 overflow-auto px-2 py-2 mb-0 d-flex flex-column gap-1">
            <li v-for="item in items" :key="item.label">
              <router-link
                :to="item.to || '#'"
                class="sidebar-btn d-flex align-items-center gap-2 px-2 py-2 rounded text-decoration-none text-dark"
                :class="{ 'bg-light fw-medium': $route.path === item.to }"
                @click="openMobile = false"
              >
                <component v-if="item.icon" :is="item.icon" style="width:16px;height:16px" />
                <span>{{ item.label }}</span>
                <span v-if="item.badge" class="badge bg-secondary ms-auto">{{ item.badge }}</span>
              </router-link>
            </li>
          </ul>
          <div v-if="$slots.footer" class="p-2 border-top">
            <slot name="footer" />
          </div>
        </nav>
      </Transition>
      <div v-if="isMobile && openMobile" class="offcanvas-backdrop fade show" @click="openMobile = false" />
    </Teleport>

    <!-- ═══ BOUTON MOBILE ═══ -->
    <button
      v-if="isMobile"
      class="btn btn-light btn-sm position-fixed p-1"
      style="top:1rem; left:1rem; z-index:1040"
      @click="openMobile = true"
    >
      <PanelLeft style="width:16px;height:16px" />
    </button>

    <!-- ═══ CONTENU PRINCIPAL ═══ -->
    <main class="flex-grow-1 overflow-auto">
      <slot name="main" />
    </main>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { PanelLeft, ChevronDown } from 'lucide-vue-next'
import { useRoute } from 'vue-router'

const props = defineProps({
  items: {
    type: Array,
    default: () => []
    // [{ label, icon, to, badge?, children?: [{ label, icon, to }] }]
  },
  defaultOpen: { type: Boolean, default: true },
  collapsible: { type: String, default: 'icon' }, // 'icon' | 'offcanvas'
})

const route = useRoute()
const isOpen = ref(props.defaultOpen)
const openMobile = ref(false)
const isMobile = ref(false)
const openGroups = ref([])

const toggleSidebar = () => { isOpen.value = !isOpen.value }
const toggleGroup = (label) => {
  const i = openGroups.value.indexOf(label)
  i === -1 ? openGroups.value.push(label) : openGroups.value.splice(i, 1)
}
const isItemActive = (item) =>
  item.children?.some(c => c.to === route.path) || item.to === route.path

const checkMobile = () => { isMobile.value = window.innerWidth < 768 }
const handleKeyDown = (e) => {
  if (e.key === 'b' && (e.metaKey || e.ctrlKey)) { e.preventDefault(); toggleSidebar() }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  window.addEventListener('keydown', handleKeyDown)
})
onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  window.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
.sidebar-btn:hover { background-color: #f8f9fa !important; }
.rotate-180 { transform: rotate(180deg); transition: transform 0.2s; }
.sidebar-tooltip {
  position: absolute; left: 110%; top: 50%; transform: translateY(-50%);
  background: #212529; color: #fff; border-radius: 4px;
  padding: 2px 8px; font-size: 0.75rem; white-space: nowrap;
  z-index: 1000; pointer-events: none; opacity: 0; transition: opacity 0.15s;
}
li:hover .sidebar-tooltip { opacity: 1; }
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s ease; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>