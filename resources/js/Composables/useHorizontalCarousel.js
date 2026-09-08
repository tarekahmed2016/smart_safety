import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

export function useHorizontalCarousel(itemsRef, options = {}) {
  const {
    cardSelector = '.px-carousel-card',
    autoSpeed = 0.8,
    manualPauseMs = 1200,
  } = options

  const { locale } = useI18n()

  const trackRef = ref(null)
  const copyCount = ref(2)
  const offset = ref(0)
  const isHovered = ref(false)
  const isManualPaused = ref(false)
  const isDragging = ref(false)
  const reduceMotion = ref(false)

  const scrollDirection = computed(() => (locale.value === 'ar' ? -1 : 1))

  let frameId = null
  let manualPauseTimer = null
  let cycleWidth = 0
  let resizeObserver = null
  let dragStartX = 0
  let dragStartOffset = 0

  const carouselItems = computed(() => {
    const items = itemsRef.value || []

    if (!items.length) {
      return []
    }

    return Array.from({ length: copyCount.value }, () => items).flat()
  })

  const getGap = () => {
    if (!trackRef.value) {
      return 20
    }

    const style = getComputedStyle(trackRef.value)
    const gapValue = style.columnGap || style.gap || '20'

    return Number.parseFloat(gapValue) || 20
  }

  const normalizeOffset = () => {
    if (cycleWidth <= 0) {
      return
    }

    while (offset.value >= cycleWidth) {
      offset.value -= cycleWidth
    }

    while (offset.value < 0) {
      offset.value += cycleWidth
    }
  }

  const applyTransform = () => {
    if (!trackRef.value) {
      return
    }

    trackRef.value.style.transform = `translate3d(${-offset.value}px, 0, 0)`
  }

  const measureCycle = () => {
    const track = trackRef.value
    const items = itemsRef.value || []

    if (!track || items.length === 0) {
      cycleWidth = 0
      return
    }

    const cards = track.querySelectorAll(cardSelector)
    const perSet = items.length

    if (cards.length < perSet) {
      return
    }

    const gap = getGap()
    let setWidth = 0

    for (let index = 0; index < perSet; index += 1) {
      setWidth += cards[index].getBoundingClientRect().width
    }

    setWidth += gap * (perSet - 1)
    cycleWidth = setWidth

    const viewport = track.parentElement?.clientWidth ?? 0
    const neededCopies = Math.max(2, Math.ceil((viewport * 2) / Math.max(setWidth, 1)) + 1)

    if (copyCount.value !== neededCopies) {
      copyCount.value = neededCopies
      nextTick(() => measureCycle())
      return
    }

    normalizeOffset()
    applyTransform()
  }

  const pauseBriefly = () => {
    isManualPaused.value = true
    clearTimeout(manualPauseTimer)
    manualPauseTimer = setTimeout(() => {
      isManualPaused.value = false
    }, manualPauseMs)
  }

  const scrollBy = (direction) => {
    const card = trackRef.value?.querySelector(cardSelector)
    const stepSize = (card?.getBoundingClientRect().width ?? 260) + getGap()

    offset.value += direction * stepSize * scrollDirection.value
    normalizeOffset()
    applyTransform()
    pauseBriefly()
  }

  const scrollPrevious = () => scrollBy(-1)
  const scrollNext = () => scrollBy(1)

  const onPointerDown = (event) => {
    if (!trackRef.value?.parentElement) {
      return
    }

    isDragging.value = true
    isManualPaused.value = true
    dragStartX = event.clientX
    dragStartOffset = offset.value
    event.currentTarget.setPointerCapture(event.pointerId)
  }

  const onPointerMove = (event) => {
    if (!isDragging.value) {
      return
    }

    const delta = (dragStartX - event.clientX) * scrollDirection.value
    offset.value = dragStartOffset + delta
    normalizeOffset()
    applyTransform()
  }

  const onPointerUp = (event) => {
    if (!isDragging.value) {
      return
    }

    isDragging.value = false
    pauseBriefly()

    if (event.currentTarget.hasPointerCapture(event.pointerId)) {
      event.currentTarget.releasePointerCapture(event.pointerId)
    }
  }

  const step = () => {
    const paused = isHovered.value || isManualPaused.value || reduceMotion.value || isDragging.value

    if (!paused) {
      offset.value += autoSpeed * scrollDirection.value
      normalizeOffset()
      applyTransform()
    }

    frameId = requestAnimationFrame(step)
  }

  onMounted(() => {
    reduceMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    nextTick(() => {
      measureCycle()

      if (trackRef.value && typeof ResizeObserver !== 'undefined') {
        resizeObserver = new ResizeObserver(() => measureCycle())
        resizeObserver.observe(trackRef.value)

        if (trackRef.value.parentElement) {
          resizeObserver.observe(trackRef.value.parentElement)
        }
      }
    })

    window.addEventListener('resize', measureCycle)
    frameId = requestAnimationFrame(step)
  })

  onUnmounted(() => {
    cancelAnimationFrame(frameId)
    window.removeEventListener('resize', measureCycle)
    resizeObserver?.disconnect()
    clearTimeout(manualPauseTimer)
  })

  watch(
    itemsRef,
    () => {
      copyCount.value = 2
      offset.value = 0
      nextTick(() => measureCycle())
    },
    { deep: true },
  )

  watch(locale, () => nextTick(() => measureCycle()))

  const setHovered = (value) => {
    isHovered.value = value
  }

  return {
    trackRef,
    carouselItems,
    isHovered,
    setHovered,
    scrollPrevious,
    scrollNext,
    onPointerDown,
    onPointerMove,
    onPointerUp,
  }
}
