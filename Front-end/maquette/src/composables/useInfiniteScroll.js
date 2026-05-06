import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

export function useInfiniteScroll({ canLoadMore, onLoadMore, rootMargin = '240px' }) {
  const sentinelRef = ref(null)
  let observer = null
  let isLoading = false

  const setupObserver = () => {
    if (observer) observer.disconnect()
    if (!sentinelRef.value) return

    observer = new IntersectionObserver(async (entries) => {
      const [entry] = entries
      if (!entry?.isIntersecting || !canLoadMore() || isLoading) return

      isLoading = true
      try {
        await onLoadMore()
        await nextTick()

        if (observer && sentinelRef.value) {
          observer.unobserve(sentinelRef.value)
          observer.observe(sentinelRef.value)
        }
      } finally {
        isLoading = false
      }
    }, { rootMargin })

    observer.observe(sentinelRef.value)
  }

  onMounted(setupObserver)
  onBeforeUnmount(() => observer?.disconnect())
  watch(() => sentinelRef.value, setupObserver)

  return { sentinelRef }
}