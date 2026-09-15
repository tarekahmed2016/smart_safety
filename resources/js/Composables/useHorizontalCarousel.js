import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { applyArrowMove, applyPointerMove, trackTranslateX } from '../Utils/carouselVisualMotion.js'

export function useHorizontalCarousel(itemsRef, options = {}) {
  const {
    cardSelector = '.px-carousel-card',
    autoSpeed = 0.8,
    manualPauseMs = 1200,
    followVisualMotion = false,
  } = options

  const { locale } = useI18n()

  const trackRef = ref(null)
  const copyCount = ref(2)
  const offset = ref(0)
  const isHovered = ref(false)
  const isManualPaused = ref(false)
  const isDragging = ref(false)
  const reduceMotion = ref(false)

  const scrollDirection = computed(() => {
    if (followVisualMotion) {
      return 1
    }

    return locale.value === 'ar' ? -1 : 1
  })

  let frameId = null
  let manualPauseTimer = null
  let cycleWidth = 0
  let resizeObserver = null
  let dragStartX = 0
  let dragStartOffset = 0
  let dragDistance = 0
  let pointerActive = false
  let activePointerId = null
  const ignoreClick = ref(false)
  const dragThreshold = 8

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

    trackRef.value.style.transform = `translate3d(${trackTranslateX(offset.value)}px, 0, 0)`
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

    if (followVisualMotion) {
      offset.value = applyArrowMove(offset.value, direction, stepSize).offset
    } else {
      offset.value += direction * stepSize * scrollDirection.value
    }

    normalizeOffset()
    applyTransform()
    pauseBriefly()
  }

  const scrollPrevious = () => scrollBy(-1)
  const scrollNext = () => scrollBy(1)

  const onKeydown = (event) => {
    if (event.key === 'ArrowLeft') {
      event.preventDefault()
      scrollPrevious()
      return
    }

    if (event.key === 'ArrowRight') {
      event.preventDefault()
      scrollNext()
    }
  }

  const onPointerDown = (event) => {
    if (!trackRef.value?.parentElement) {
      return
    }

    if (event.target.closest('a, button')) {
      return
    }

    pointerActive = true
    activePointerId = event.pointerId
    isDragging.value = false
    isManualPaused.value = true
    ignoreClick.value = false
    dragDistance = 0
    dragStartX = event.clientX
    dragStartOffset = offset.value
  }

  const onPointerMove = (event) => {
    if (!pointerActive || event.pointerId !== activePointerId) {
      return
    }

    dragDistance = Math.max(dragDistance, Math.abs(event.clientX - dragStartX))

    if (!isDragging.value) {
      if (dragDistance <= dragThreshold) {
        return
      }

      isDragging.value = true
      ignoreClick.value = true

      try {
        if (event.currentTarget?.setPointerCapture) {
          event.currentTarget.setPointerCapture(event.pointerId)
        }
      } catch {
        // Synthetic or inactive pointers cannot capture; dragging still updates offset.
      }
    }

    if (followVisualMotion) {
      offset.value = applyPointerMove(dragStartOffset, dragStartX, event.clientX).offset
    } else {
      offset.value = dragStartOffset + (dragStartX - event.clientX) * scrollDirection.value
    }
    normalizeOffset()
    applyTransform()
  }

  const onPointerUp = (event) => {
    if (!pointerActive || (activePointerId !== null && event.pointerId !== activePointerId)) {
      return
    }

    pointerActive = false
    activePointerId = null

    if (isDragging.value && event.currentTarget?.hasPointerCapture?.(event.pointerId)) {
      try {
        event.currentTarget.releasePointerCapture(event.pointerId)
      } catch {
        // Ignore capture release errors from synthetic pointer events.
      }
    }

    isDragging.value = false
    pauseBriefly()
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
    onKeydown,
    onPointerDown,
    onPointerMove,
    onPointerUp,
    ignoreClick,
  }
}
