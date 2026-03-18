<template>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li
        v-for="(item, index) in items"
        :key="index"
        class="breadcrumb-item"
        :class="{ active: index === items.length - 1 }"
      >
        <!-- Dernier élément = page active -->
        <span v-if="index === items.length - 1">{{ item.label }}</span>
        <!-- Lien cliquable -->
        <router-link v-else-if="item.to" :to="item.to">{{ item.label }}</router-link>
        <a v-else :href="item.href">{{ item.label }}</a>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    // [{ label: 'Accueil', to: '/' }, { label: 'Missions', to: '/missions' }, { label: 'Détail' }]
    default: () => []
  }
})
</script>