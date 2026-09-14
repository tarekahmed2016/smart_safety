import { onUnmounted, watch } from 'vue'

/**
 * Shared dialog behavior: body scroll lock and Escape-to-close.
 *
 * @param {import('vue').Ref<boolean>|import('vue').ComputedRef<boolean>} isOpen
 * @param {() => void} onClose
 */
export function useDialogAccessibility(isOpen, onClose) {
  const handleEscape = (event) => {
    if (event.key === 'Escape' && isOpen.value) {
      event.preventDefault()
      onClose()
    }
  }

  const lockBackgroundScroll = () => {
    document.documentElement.style.overflow = 'hidden'
    document.body.style.overflow = 'hidden'
    document.body.style.overscrollBehavior = 'none'
  }

  const unlockBackgroundScroll = () => {
    document.documentElement.style.overflow = ''
    document.body.style.overflow = ''
    document.body.style.overscrollBehavior = ''
  }

  watch(isOpen, (open) => {
    if (open) {
      lockBackgroundScroll()
      document.addEventListener('keydown', handleEscape)
    } else {
      unlockBackgroundScroll()
      document.removeEventListener('keydown', handleEscape)
    }
  }, { immediate: true })

  onUnmounted(() => {
    unlockBackgroundScroll()
    document.removeEventListener('keydown', handleEscape)
  })
}
