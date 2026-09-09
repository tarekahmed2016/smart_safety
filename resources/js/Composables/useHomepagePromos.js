import { router, useForm } from '@inertiajs/vue3'

const buildPromoUpdatePayload = (item, overrides = {}) => ({
  type: item.type?.value || item.type,
  title_ar: item.title_ar || '',
  title_en: item.title_en || '',
  description_ar: item.description_ar || '',
  description_en: item.description_en || '',
  cta_text_ar: item.cta_text_ar || '',
  cta_text_en: item.cta_text_en || '',
  cta_url: item.cta_url || '',
  layout_variant: item.layout_variant?.value || item.layout_variant || 'content_left',
  icon: item.icon || '',
  ordering: item.ordering ?? 0,
  is_active: Boolean(item.is_active),
  ...overrides,
})

export function useHomepagePromos() {
  const deleteForm = useForm({})

  const fetchNextOrdering = async (type) => {
    const response = await fetch(route('homepage-promos.next-ordering', { type }), {
      headers: { Accept: 'application/json' },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch next ordering')
    }

    const data = await response.json()

    return data.ordering
  }

  const createHomepagePromo = (promoData, options = {}) => {
    return router.post(route('homepage-promos.store'), promoData, {
      preserveScroll: true,
      ...options,
    })
  }

  const updateHomepagePromo = (id, promoData, options = {}) => {
    return router.put(route('homepage-promos.update', id), promoData, {
      preserveScroll: true,
      ...options,
    })
  }

  const deleteHomepagePromo = (promoId, callbacks = {}) => {
    return deleteForm.delete(route('homepage-promos.destroy', promoId), {
      preserveScroll: true,
      ...callbacks,
    })
  }

  const postPromoUpdate = (item, overrides = {}, callbacks = {}) => {
    router.post(route('homepage-promos.update', item.id), {
      ...buildPromoUpdatePayload(item, overrides),
      _method: 'put',
    }, {
      preserveScroll: true,
      ...callbacks,
    })
  }

  const toggleHomepagePromoActive = (item, callbacks = {}) => {
    postPromoUpdate(item, { is_active: !item.is_active }, callbacks)
  }

  const moveHomepagePromoItem = (items, item, direction, callbacks = {}) => {
    const sortedItems = [...items].sort((left, right) => left.ordering - right.ordering)
    const currentIndex = sortedItems.findIndex((entry) => entry.id === item.id)
    const targetIndex = currentIndex + direction

    if (currentIndex < 0 || targetIndex < 0 || targetIndex >= sortedItems.length) {
      return
    }

    const targetItem = sortedItems[targetIndex]

    postPromoUpdate(item, { ordering: targetItem.ordering }, {
      onSuccess: () => {
        postPromoUpdate(targetItem, { ordering: item.ordering }, callbacks)
      },
    })
  }

  return {
    deleteForm,
    fetchNextOrdering,
    createHomepagePromo,
    updateHomepagePromo,
    deleteHomepagePromo,
    toggleHomepagePromoActive,
    moveHomepagePromoItem,
  }
}
