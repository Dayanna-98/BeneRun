<template>
  <div v-if="embedUrl" class="d-flex flex-column gap-2">
    <a
      :href="mapsLink"
      target="_blank"
      rel="noopener noreferrer"
      class="small text-primary d-inline-flex align-items-center gap-1 text-decoration-none"
    >
      <MapPinned style="width:14px;height:14px" />
      {{ linkLabel }}
    </a>

    <div class="position-relative overflow-hidden rounded border bg-white" :style="containerStyle">
      <iframe
        :src="embedUrl"
        width="100%"
        height="100%"
        frameborder="0"
        style="border:0"
        allowfullscreen
      />

      <div
        v-if="showPerimeter"
        class="position-absolute top-50 start-50 translate-middle rounded-circle border border-2 border-danger-subtle bg-danger-subtle"
        :style="circleStyle"
      />

      <div
        v-if="showPerimeter"
        class="position-absolute start-0 end-0 bottom-0 px-3 py-2 small bg-white bg-opacity-75 border-top"
      >
        Périmètre affiché: {{ formattedRadius }}
      </div>
    </div>
  </div>

  <div v-else class="small text-muted">
    Collez un lien Google Maps contenant un point précis pour afficher l'aperçu.
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { MapPinned } from 'lucide-vue-next'
import {
  buildGoogleMapsEmbedUrl,
  buildGoogleMapsLink,
  formatRadius,
} from '@/utils/googleMaps'

const props = defineProps({
  mapsUrl: {
    type: String,
    default: '',
  },
  radiusMeters: {
    type: [Number, String],
    default: null,
  },
  height: {
    type: Number,
    default: 220,
  },
  linkLabel: {
    type: String,
    default: 'Voir sur Google Maps',
  },
})

const embedUrl = computed(() => buildGoogleMapsEmbedUrl(props.mapsUrl, { radiusMeters: props.radiusMeters }))
const mapsLink = computed(() => buildGoogleMapsLink(props.mapsUrl))
const formattedRadius = computed(() => formatRadius(props.radiusMeters))
const showPerimeter = computed(() => Boolean(formattedRadius.value))

const containerStyle = computed(() => ({ height: `${props.height}px` }))

const circleStyle = computed(() => {
  const diameter = Math.min(Math.max(props.height * 0.58, 110), props.height - 36)

  return {
    width: `${diameter}px`,
    height: `${diameter}px`,
  }
})
</script>