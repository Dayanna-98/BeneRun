<template>
  <div :id="id" class="carousel slide" :class="className" data-bs-ride="carousel">

    <!-- Indicateurs -->
    <div v-if="showIndicators" class="carousel-indicators">
      <button
        v-for="(item, index) in items"
        :key="index"
        type="button"
        :data-bs-target="`#${id}`"
        :data-bs-slide-to="index"
        :class="{ active: index === 0 }"
      />
    </div>

    <!-- Slides -->
    <div class="carousel-inner">
      <div
        v-for="(item, index) in items"
        :key="index"
        class="carousel-item"
        :class="{ active: index === 0 }"
      >
        <img v-if="item.image" :src="item.image" class="d-block w-100" :alt="item.caption || ''" />
        <div v-if="item.caption" class="carousel-caption d-none d-md-block">
          <p>{{ item.caption }}</p>
        </div>
        <slot v-if="!item.image" :item="item" />
      </div>
    </div>

    <!-- Contrôles -->
    <button v-if="showControls" class="carousel-control-prev" type="button" :data-bs-target="`#${id}`" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" />
      <span class="visually-hidden">Précédent</span>
    </button>
    <button v-if="showControls" class="carousel-control-next" type="button" :data-bs-target="`#${id}`" data-bs-slide="next">
      <span class="carousel-control-next-icon" />
      <span class="visually-hidden">Suivant</span>
    </button>

  </div>
</template>

<script setup>
defineProps({
  id: { type: String, default: () => `carousel-${Math.random().toString(36).slice(2)}` },
  items: { type: Array, default: () => [] }, // [{ image, caption }]
  showIndicators: { type: Boolean, default: true },
  showControls: { type: Boolean, default: true },
  className: { type: String, default: '' },
})
</script>