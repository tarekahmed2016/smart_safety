import { computed } from 'vue'

const DEFAULT_SECTIONS = [
  { key: 'hero', type: 'hero', title_ar: '', title_en: '', settings: {} },
  { key: 'features', type: 'features', title_ar: '', title_en: '', settings: {} },
  { key: 'products', type: 'products', title_ar: '', title_en: '', settings: {} },
  { key: 'custom_manufacturing', type: 'custom_manufacturing', title_ar: '', title_en: '', settings: {} },
  { key: 'industries', type: 'industries', title_ar: '', title_en: '', settings: {} },
  { key: 'about', type: 'about', title_ar: '', title_en: '', settings: {} },
  { key: 'gallery', type: 'gallery', title_ar: '', title_en: '', settings: { max_items: 8 } },
  { key: 'contact_cta', type: 'contact_cta', title_ar: '', title_en: '', settings: {} },
  { key: 'contact', type: 'contact_form', title_ar: '', title_en: '', settings: {} },
]

const isSplitPair = (current, next) => (
  current
  && next
  && (
    (current.type === 'custom_manufacturing' && next.type === 'industries')
    || (current.type === 'industries' && next.type === 'custom_manufacturing')
  )
)

export function useHomepageSections(homepageSections) {
  const normalizedSections = computed(() => {
    const source = homepageSections.value?.length ? homepageSections.value : DEFAULT_SECTIONS
    const result = []

    for (let index = 0; index < source.length; index += 1) {
      const current = source[index]
      const next = source[index + 1]

      if (isSplitPair(current, next)) {
        const customManufacturing = current.type === 'custom_manufacturing' ? current : next
        const industries = current.type === 'industries' ? current : next

        result.push({
          key: `split-${customManufacturing.key}-${industries.key}`,
          type: 'split',
          customManufacturing,
          industries,
        })
        index += 1
        continue
      }

      result.push(current)
    }

    return result
  })

  return {
    normalizedSections,
  }
}
