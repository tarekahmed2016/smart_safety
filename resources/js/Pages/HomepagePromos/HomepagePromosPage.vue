<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import HomepagePromoSectionCard from '../../Components/Features/HomepagePromos/HomepagePromoSectionCard.vue'
import HomepagePromoFormModal from '../../Components/Features/HomepagePromos/HomepagePromoFormModal.vue'
import HomepagePromoDeleteModal from '../../Components/Features/HomepagePromos/HomepagePromoDeleteModal.vue'
import { useHomepagePromos } from '../../Composables/useHomepagePromos.js'
import { useModal } from '../../Composables/General/useModal.js'

const { t } = useI18n()
const page = usePage()

const sectionCards = computed(() => page.props.sectionCards || [])

const { deleteForm, deleteHomepagePromo, fetchNextOrdering, toggleHomepagePromoActive, moveHomepagePromoItem } = useHomepagePromos()

const pendingCard = ref(null)
const lockedPromoType = ref(null)

const formModal = useModal({
  onOpen: async (item) => {
    if (item) {
      return { ordering: item.ordering, type: item.type?.value || item.type }
    }

    const type = pendingCard.value?.default_promo_type || lockedPromoType.value || 'feature_highlight'
    const ordering = await fetchNextOrdering(type)

    return { ordering, type }
  },
})

const deleteModal = useModal()

const filteredPromoTypes = computed(() => {
  const allTypes = page.props.promoTypes || []

  if (!lockedPromoType.value) {
    return allTypes
  }

  return allTypes.filter((promoType) => promoType.value === lockedPromoType.value)
})

const openAddPromo = (card) => {
  pendingCard.value = card
  lockedPromoType.value = card.default_promo_type
  formModal.open()
}

const openEditPromo = (item) => {
  lockedPromoType.value = item.type?.value || item.type
  formModal.open(item)
}

const closeFormModal = () => {
  formModal.close()
  pendingCard.value = null
  lockedPromoType.value = null
}

const handleDeleteConfirm = () => {
  if (!deleteModal.selectedItem.value) return

  deleteHomepagePromo(deleteModal.selectedItem.value.id, {
    onSuccess: () => deleteModal.close(),
  })
}

const handleTogglePromo = (item) => {
  toggleHomepagePromoActive(item)
}

const handleMovePromo = (card, item, direction) => {
  moveHomepagePromoItem(card.items, item, direction)
}
</script>

<template>
  <div class="bg-gray-50 dark:bg-gray-900 p-3 md:p-6">
    <div class="max-w-5xl mx-auto">
      <div class="mb-6 md:mb-8">
        <h1 class="text-page-title text-gray-900 dark:text-gray-100">{{ t('homepagePromos.pageTitle') }}</h1>
        <p class="mt-2 text-muted muted-color">{{ t('homepagePromos.pageSubtitle') }}</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 md:p-6">
        <p class="mb-6 text-muted muted-color">{{ t('homepagePromos.help') }}</p>

        <div class="space-y-4">
          <HomepagePromoSectionCard
            v-for="card in sectionCards"
            :key="card.key"
            :card="card"
            @add-promo="openAddPromo"
            @edit-promo="openEditPromo"
            @delete-promo="deleteModal.open"
            @toggle-promo="handleTogglePromo"
            @move-promo="handleMovePromo"
          />
        </div>
      </div>
    </div>

    <HomepagePromoFormModal
      :isOpen="formModal.isOpen.value"
      :homepagePromo="formModal.selectedItem.value"
      :nextData="formModal.extraData.value"
      :defaultType="lockedPromoType || 'feature_highlight'"
      :promoTypes="filteredPromoTypes"
      :lockType="Boolean(lockedPromoType)"
      @close="closeFormModal"
    />

    <HomepagePromoDeleteModal
      :isOpen="deleteModal.isOpen.value"
      :homepagePromo="deleteModal.selectedItem.value"
      :loading="deleteForm.processing"
      @close="deleteModal.close"
      @confirm="handleDeleteConfirm"
    />
  </div>
</template>
