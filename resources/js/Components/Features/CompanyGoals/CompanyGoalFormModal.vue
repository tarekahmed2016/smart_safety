<template>
  <DashboardModalShell
    :isOpen="isOpen"
    title-id="company-goal-form-modal-title"
    @close="handleClose"
  >
    <form @submit.prevent="submit">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 id="company-goal-form-modal-title" class="text-card-title text-gray-900 dark:text-gray-100">
          {{ companyGoal ? t('companyGoals.form.editTitle') : t('companyGoals.form.addTitle') }}
        </h2>
      </div>

      <div class="px-6 py-4 space-y-4">
        <div>
          <label class="form-label text-label">
            {{ t('companyGoals.form.textArLabel') }} <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.text_ar"
            rows="3"
            required
            class="form-input text-body"
            :placeholder="t('companyGoals.form.textArPlaceholder')"
          />
          <p v-if="form.errors.text_ar" class="mt-1 text-sm text-red-600">{{ form.errors.text_ar }}</p>
        </div>

        <div>
          <label class="form-label text-label">
            {{ t('companyGoals.form.textEnLabel') }} <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.text_en"
            rows="3"
            required
            class="form-input text-body"
            :placeholder="t('companyGoals.form.textEnPlaceholder')"
          />
          <p v-if="form.errors.text_en" class="mt-1 text-sm text-red-600">{{ form.errors.text_en }}</p>
        </div>

        <div>
          <label class="form-label text-label">
            {{ t('companyGoals.form.orderingLabel') }} <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.ordering"
            type="number"
            min="0"
            required
            class="form-input text-body"
            :placeholder="t('companyGoals.form.orderingPlaceholder')"
          />
          <p v-if="form.errors.ordering" class="mt-1 text-sm text-red-600">{{ form.errors.ordering }}</p>
        </div>

        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
          <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
          <span class="text-label">{{ t('companyGoals.form.activeLabel') }}</span>
        </label>
      </div>

      <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
        <button type="button" class="btn btn-secondary px-4 py-2" :disabled="form.processing" @click="handleClose">
          {{ t('companyGoals.form.cancel') }}
        </button>
        <button type="submit" class="btn btn-primary px-4 py-2" :disabled="form.processing">
          {{ form.processing ? t('companyGoals.form.saving') : t('companyGoals.form.save') }}
        </button>
      </div>
    </form>
  </DashboardModalShell>
</template>

<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import DashboardModalShell from '../../Common/DashboardModalShell.vue'

const { t } = useI18n()

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  companyGoal: {
    type: Object,
    default: null,
  },
  nextOrdering: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['close'])

const form = useForm({
  text_ar: '',
  text_en: '',
  ordering: '',
  is_active: true,
})

watch(() => props.isOpen, (isOpen) => {
  if (!isOpen) return

  if (props.companyGoal) {
    form.text_ar = props.companyGoal.text_ar || ''
    form.text_en = props.companyGoal.text_en || ''
    form.ordering = props.companyGoal.ordering ?? ''
    form.is_active = Boolean(props.companyGoal.is_active)
  } else {
    form.reset()
    form.is_active = true
    form.ordering = props.nextOrdering ?? ''
  }
})

const handleClose = () => {
  form.clearErrors()
  emit('close')
}

const submit = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => handleClose(),
  }

  if (props.companyGoal) {
    form.put(route('company-goals.update', props.companyGoal.id), options)
    return
  }

  form.post(route('company-goals.store'), options)
}
</script>
