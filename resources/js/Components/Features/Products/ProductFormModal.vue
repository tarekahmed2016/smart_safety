<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import DashboardModalShell from '../../Common/DashboardModalShell.vue'
import RichTextEditor from '../../Common/asyncRichTextEditor.js'

const { t } = useI18n()

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  product: {
    type: Object,
    default: null
  },
  nextOrdering: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['close'])

const form = useForm({
  name_ar: '',
  name_en: '',
  slug: '',
  description_ar: '',
  description_en: '',
  details_ar: '',
  details_en: '',
  sizes: [],
  specifications_ar: [],
  specifications_en: [],
  ordering: '',
  is_active: true,
  show_on_homepage: true,
  image: null,
})

const imagePreview = ref(null)
const imageInput = ref(null)
const imageFileName = ref(null)

const cloneSizes = (sizes) => (Array.isArray(sizes) ? sizes.map((size) => ({
  value: size?.value || '',
  unit: size?.unit || '',
})) : [])

const cloneSpecs = (items) => (Array.isArray(items) ? items.map((item) => String(item || '')) : [])

const addSize = () => {
  form.sizes.push({ value: '', unit: 'mm' })
}

const removeSize = (index) => {
  form.sizes.splice(index, 1)
}

const moveSize = (index, direction) => {
  const target = index + direction
  if (target < 0 || target >= form.sizes.length) return
  const items = [...form.sizes]
  const [moved] = items.splice(index, 1)
  items.splice(target, 0, moved)
  form.sizes = items
}

const addSpecification = (field) => {
  form[field].push('')
}

const removeSpecification = (field, index) => {
  form[field].splice(index, 1)
}

const moveSpecification = (field, index, direction) => {
  const target = index + direction
  if (target < 0 || target >= form[field].length) return
  const items = [...form[field]]
  const [moved] = items.splice(index, 1)
  items.splice(target, 0, moved)
  form[field] = items
}

const handleImageChange = (event) => {
  const file = event.target.files[0] || null
  form.image = file
  imageFileName.value = file?.name || null
  imagePreview.value = file ? URL.createObjectURL(file) : null
}

watch(() => props.isOpen, (isOpen) => {
  if (!isOpen) return

  if (props.product) {
    form.name_ar = props.product.name_ar || ''
    form.name_en = props.product.name_en || ''
    form.slug = props.product.slug || ''
    form.description_ar = props.product.description_ar || ''
    form.description_en = props.product.description_en || ''
    form.details_ar = props.product.details_ar || ''
    form.details_en = props.product.details_en || ''
    form.sizes = cloneSizes(props.product.sizes)
    form.specifications_ar = cloneSpecs(props.product.specifications_ar)
    form.specifications_en = cloneSpecs(props.product.specifications_en)
    form.ordering = props.product.ordering ?? ''
    form.is_active = Boolean(props.product.is_active)
    form.show_on_homepage = Boolean(props.product.show_on_homepage)
    form.image = null
    imagePreview.value = props.product.attachment?.asset_path || null
    imageFileName.value = null
  } else {
    form.reset()
    form.is_active = true
    form.show_on_homepage = true
    form.ordering = props.nextOrdering ?? ''
    imagePreview.value = null
    imageFileName.value = null
  }

  if (imageInput.value) {
    imageInput.value.value = ''
  }

  form.clearErrors()
}, { immediate: true })

const submit = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('close')
    }
  }

  props.product
    ? form.transform((data) => ({ ...data, _method: 'put' })).post(route('products.update', props.product.id), options)
    : form.post(route('products.store'), options)
}

const handleClose = () => {
  form.reset()
  form.clearErrors()
  imagePreview.value = null
  imageFileName.value = null
  emit('close')
}
</script>

<template>
  <DashboardModalShell
    :isOpen="isOpen"
    title-id="product-form-modal-title"
    @close="handleClose"
  >
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 id="product-form-modal-title" class="text-card-title text-gray-900 dark:text-gray-100">
          {{ product ? t('products.form.editTitle') : t('products.form.addTitle') }}
        </h2>
        <button
          @click="handleClose"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="submit" class="px-6 py-4 space-y-4 max-h-[75vh] overflow-y-auto">
        <div class="space-y-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <h3 class="text-label font-medium text-gray-900 dark:text-gray-100">{{ t('bilingual.arabic') }}</h3>

          <div>
            <label class="form-label text-label">
              {{ t('products.form.nameArLabel') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name_ar"
              type="text"
              required
              class="form-input text-body"
              :placeholder="t('products.form.nameArPlaceholder')"
            />
            <p v-if="form.errors.name_ar" class="form-error">{{ form.errors.name_ar }}</p>
          </div>

          <div>
            <label class="form-label text-label">{{ t('products.form.descriptionArLabel') }}</label>
            <RichTextEditor
              v-model="form.description_ar"
              :active="isOpen"
              dir="rtl"
              :placeholder="t('products.form.descriptionArPlaceholder')"
            />
            <p v-if="form.errors.description_ar" class="form-error">{{ form.errors.description_ar }}</p>
          </div>
        </div>

        <div class="space-y-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <h3 class="text-label font-medium text-gray-900 dark:text-gray-100">{{ t('bilingual.english') }}</h3>

          <div>
            <label class="form-label text-label">
              {{ t('products.form.nameEnLabel') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name_en"
              type="text"
              required
              class="form-input text-body"
              :placeholder="t('products.form.nameEnPlaceholder')"
            />
            <p v-if="form.errors.name_en" class="form-error">{{ form.errors.name_en }}</p>
          </div>

          <div>
            <label class="form-label text-label">{{ t('products.form.descriptionEnLabel') }}</label>
            <RichTextEditor
              v-model="form.description_en"
              :active="isOpen"
              dir="ltr"
              :placeholder="t('products.form.descriptionEnPlaceholder')"
            />
            <p v-if="form.errors.description_en" class="form-error">{{ form.errors.description_en }}</p>
          </div>
        </div>

        <div class="space-y-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <h3 class="text-label font-medium text-gray-900 dark:text-gray-100">{{ t('products.form.detailsSectionTitle') }}</h3>

          <div>
            <label class="form-label text-label">{{ t('products.form.detailsArLabel') }}</label>
            <RichTextEditor
              v-model="form.details_ar"
              :active="isOpen"
              dir="rtl"
              :placeholder="t('products.form.detailsArPlaceholder')"
            />
            <p v-if="form.errors.details_ar" class="form-error">{{ form.errors.details_ar }}</p>
          </div>

          <div>
            <label class="form-label text-label">{{ t('products.form.detailsEnLabel') }}</label>
            <RichTextEditor
              v-model="form.details_en"
              :active="isOpen"
              dir="ltr"
              :placeholder="t('products.form.detailsEnPlaceholder')"
            />
            <p v-if="form.errors.details_en" class="form-error">{{ form.errors.details_en }}</p>
          </div>

          <div>
            <div class="flex items-center justify-between gap-3 mb-2">
              <label class="form-label text-label mb-0">{{ t('products.form.sizesLabel') }}</label>
              <button type="button" class="btn btn-secondary px-3 py-1.5" @click="addSize">
                {{ t('products.form.addSize') }}
              </button>
            </div>
            <div v-if="form.sizes.length" class="space-y-2">
              <div v-for="(size, index) in form.sizes" :key="`size-${index}`" class="grid grid-cols-[1fr_1fr_auto] gap-2 items-center">
                <input
                  v-model="size.value"
                  type="text"
                  class="form-input text-body"
                  :placeholder="t('products.form.sizeValuePlaceholder')"
                />
                <input
                  v-model="size.unit"
                  type="text"
                  dir="ltr"
                  class="form-input text-body"
                  :placeholder="t('products.form.sizeUnitPlaceholder')"
                />
                <div class="flex items-center gap-1">
                  <button type="button" class="btn btn-secondary px-2 py-1.5" :disabled="index === 0" @click="moveSize(index, -1)">↑</button>
                  <button type="button" class="btn btn-secondary px-2 py-1.5" :disabled="index === form.sizes.length - 1" @click="moveSize(index, 1)">↓</button>
                  <button type="button" class="btn btn-danger px-2 py-1.5" @click="removeSize(index)">{{ t('products.form.removeItem') }}</button>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-muted muted-color">{{ t('products.form.sizesEmpty') }}</p>
            <p v-if="form.errors.sizes" class="form-error">{{ form.errors.sizes }}</p>
          </div>

          <div>
            <div class="flex items-center justify-between gap-3 mb-2">
              <label class="form-label text-label mb-0">{{ t('products.form.specificationsArLabel') }}</label>
              <button type="button" class="btn btn-secondary px-3 py-1.5" @click="addSpecification('specifications_ar')">
                {{ t('products.form.addSpecification') }}
              </button>
            </div>
            <div v-if="form.specifications_ar.length" class="space-y-2">
              <div v-for="(item, index) in form.specifications_ar" :key="`spec-ar-${index}`" class="flex items-center gap-2">
                <input v-model="form.specifications_ar[index]" type="text" dir="rtl" class="form-input text-body" :placeholder="t('products.form.specificationPlaceholder')" />
                <button type="button" class="btn btn-secondary px-2 py-1.5" :disabled="index === 0" @click="moveSpecification('specifications_ar', index, -1)">↑</button>
                <button type="button" class="btn btn-secondary px-2 py-1.5" :disabled="index === form.specifications_ar.length - 1" @click="moveSpecification('specifications_ar', index, 1)">↓</button>
                <button type="button" class="btn btn-danger px-2 py-1.5" @click="removeSpecification('specifications_ar', index)">{{ t('products.form.removeItem') }}</button>
              </div>
            </div>
            <p v-else class="text-sm text-muted muted-color">{{ t('products.form.specificationsEmpty') }}</p>
            <p v-if="form.errors.specifications_ar" class="form-error">{{ form.errors.specifications_ar }}</p>
          </div>

          <div>
            <div class="flex items-center justify-between gap-3 mb-2">
              <label class="form-label text-label mb-0">{{ t('products.form.specificationsEnLabel') }}</label>
              <button type="button" class="btn btn-secondary px-3 py-1.5" @click="addSpecification('specifications_en')">
                {{ t('products.form.addSpecification') }}
              </button>
            </div>
            <div v-if="form.specifications_en.length" class="space-y-2">
              <div v-for="(item, index) in form.specifications_en" :key="`spec-en-${index}`" class="flex items-center gap-2">
                <input v-model="form.specifications_en[index]" type="text" dir="ltr" class="form-input text-body" :placeholder="t('products.form.specificationPlaceholder')" />
                <button type="button" class="btn btn-secondary px-2 py-1.5" :disabled="index === 0" @click="moveSpecification('specifications_en', index, -1)">↑</button>
                <button type="button" class="btn btn-secondary px-2 py-1.5" :disabled="index === form.specifications_en.length - 1" @click="moveSpecification('specifications_en', index, 1)">↓</button>
                <button type="button" class="btn btn-danger px-2 py-1.5" @click="removeSpecification('specifications_en', index)">{{ t('products.form.removeItem') }}</button>
              </div>
            </div>
            <p v-else class="text-sm text-muted muted-color">{{ t('products.form.specificationsEmpty') }}</p>
            <p v-if="form.errors.specifications_en" class="form-error">{{ form.errors.specifications_en }}</p>
          </div>
        </div>

        <div>
          <label class="form-label text-label">{{ t('products.form.slugLabel') }}</label>
          <input
            v-model="form.slug"
            type="text"
            class="form-input text-body"
            :placeholder="t('products.form.slugPlaceholder')"
          />
          <p class="text-muted muted-color mt-1">{{ t('products.form.slugHint') }}</p>
          <p v-if="form.errors.slug" class="form-error">{{ form.errors.slug }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label text-label">
              {{ t('products.form.orderingLabel') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.ordering"
              type="number"
              min="0"
              required
              class="form-input text-body"
              :placeholder="t('products.form.orderingPlaceholder')"
            />
            <p v-if="form.errors.ordering" class="form-error">{{ form.errors.ordering }}</p>
          </div>

          <div class="flex items-end pb-2">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-label">{{ t('products.form.activeLabel') }}</span>
            </label>
            <p v-if="form.errors.is_active" class="form-error ms-2">{{ form.errors.is_active }}</p>
          </div>
        </div>

        <div>
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input
              v-model="form.show_on_homepage"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="text-label">{{ t('products.form.showOnHomepageLabel') }}</span>
          </label>
          <p v-if="form.errors.show_on_homepage" class="form-error">{{ form.errors.show_on_homepage }}</p>
        </div>

        <div>
          <label class="form-label text-label">
            {{ t('products.form.imageLabel') }} <span v-if="!product" class="text-red-500">*</span>
          </label>
          <div class="flex items-center gap-4">
            <img
              v-if="imagePreview"
              :src="imagePreview"
              alt="Product image preview"
              class="h-16 rounded-md border border-gray-200 dark:border-gray-700 object-cover"
            />
            <div class="flex flex-col gap-1.5 flex-1">
              <button
                type="button"
                @click="imageInput.click()"
                class="btn btn-secondary px-4 py-2 w-full cursor-pointer"
              >
                {{ t('products.form.chooseFile') }}
              </button>
              <span class="text-sm text-muted muted-color truncate text-center">
                {{ imageFileName || t('products.form.noFileChosen') }}
              </span>
              <input
                ref="imageInput"
                type="file"
                accept="image/*"
                :required="!product"
                @change="handleImageChange"
                class="hidden"
              />
            </div>
          </div>
          <p v-if="form.errors.image" class="form-error">{{ form.errors.image }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
          <button
            type="button"
            @click="handleClose"
            :disabled="form.processing"
            class="btn btn-secondary px-4 py-2"
          >
            {{ t('products.form.cancel') }}
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn btn-primary px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ form.processing ? t('products.form.saving') : t('products.form.save') }}
          </button>
        </div>
      </form>
  </DashboardModalShell>
</template>
