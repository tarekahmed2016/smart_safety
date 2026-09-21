<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { companyNameBrandCssVars, companyNameBrandArFontStyle, companyNameBrandEnFontStyle, COMPANY_NAME_BRAND_DEFAULTS } from '../../../Composables/useCompanyNameBrandTypography.js'

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  logoPreview: {
    type: String,
    default: null,
  },
})

const { t } = useI18n()

const fontWeightOptions = [400, 500, 600, 700, 800, 900]

const previewCompanyInfo = computed(() => ({
  name_ar: props.form.name_ar,
  name_en: props.form.name_en,
  company_name_font_family_ar: props.form.company_name_font_family_ar,
  company_name_font_family_en: props.form.company_name_font_family_en,
  company_name_font_size_ar: props.form.company_name_font_size_ar,
  company_name_font_size_en: props.form.company_name_font_size_en,
  company_name_font_weight: props.form.company_name_font_weight,
  company_name_text_color: props.form.company_name_text_color,
}))

const previewNameAr = computed(() => props.form.name_ar || t('public.home.defaultCompanyName'))
const previewNameEn = computed(() => props.form.name_en || t('public.home.defaultCompanyName'))
const previewStyle = computed(() => companyNameBrandCssVars(previewCompanyInfo.value))
const previewArFontStyle = computed(() => companyNameBrandArFontStyle(previewCompanyInfo.value))
const previewEnFontStyle = computed(() => companyNameBrandEnFontStyle(previewCompanyInfo.value))
const previewLogo = computed(() => props.logoPreview || '')
</script>

<template>
  <div class="space-y-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
    <div>
      <h3 class="text-label font-medium text-gray-900 dark:text-gray-100">{{ t('companyInfo.form.companyNameBrandTitle') }}</h3>
      <p class="mt-1 text-sm text-muted muted-color">{{ t('companyInfo.form.companyNameBrandHelp') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="form-label text-label">{{ t('companyInfo.form.companyNameFontFamilyArLabel') }}</label>
        <input
          v-model="form.company_name_font_family_ar"
          type="text"
          dir="ltr"
          class="form-input text-body font-mono"
          :placeholder="t('companyInfo.form.companyNameFontFamilyArPlaceholder')"
        />
        <p class="mt-1 text-sm text-muted muted-color">{{ t('companyInfo.form.companyNameFontFamilyHelp') }}</p>
        <p v-if="form.errors.company_name_font_family_ar" class="form-error">{{ form.errors.company_name_font_family_ar }}</p>
      </div>

      <div>
        <label class="form-label text-label">{{ t('companyInfo.form.companyNameFontFamilyEnLabel') }}</label>
        <input
          v-model="form.company_name_font_family_en"
          type="text"
          dir="ltr"
          class="form-input text-body font-mono"
          :placeholder="t('companyInfo.form.companyNameFontFamilyEnPlaceholder')"
        />
        <p class="mt-1 text-sm text-muted muted-color">{{ t('companyInfo.form.companyNameFontFamilyHelp') }}</p>
        <p v-if="form.errors.company_name_font_family_en" class="form-error">{{ form.errors.company_name_font_family_en }}</p>
      </div>

      <div>
        <label class="form-label text-label">{{ t('companyInfo.form.companyNameFontSizeArLabel') }}</label>
        <input
          v-model="form.company_name_font_size_ar"
          type="number"
          min="0.5"
          max="3"
          step="0.01"
          dir="ltr"
          class="form-input text-body"
          :placeholder="String(COMPANY_NAME_BRAND_DEFAULTS.font_size_ar)"
        />
        <p v-if="form.errors.company_name_font_size_ar" class="form-error">{{ form.errors.company_name_font_size_ar }}</p>
      </div>

      <div>
        <label class="form-label text-label">{{ t('companyInfo.form.companyNameFontSizeEnLabel') }}</label>
        <input
          v-model="form.company_name_font_size_en"
          type="number"
          min="0.5"
          max="3"
          step="0.01"
          dir="ltr"
          class="form-input text-body"
          :placeholder="String(COMPANY_NAME_BRAND_DEFAULTS.font_size_en)"
        />
        <p v-if="form.errors.company_name_font_size_en" class="form-error">{{ form.errors.company_name_font_size_en }}</p>
      </div>

      <div>
        <label class="form-label text-label">{{ t('companyInfo.form.companyNameFontWeightLabel') }}</label>
        <select v-model="form.company_name_font_weight" class="form-input text-body">
          <option value="">{{ t('companyInfo.form.companyNameFontWeightDefault') }}</option>
          <option v-for="weight in fontWeightOptions" :key="weight" :value="String(weight)">{{ weight }}</option>
        </select>
        <p v-if="form.errors.company_name_font_weight" class="form-error">{{ form.errors.company_name_font_weight }}</p>
      </div>

      <div>
        <label class="form-label text-label">{{ t('companyInfo.form.companyNameTextColorLabel') }}</label>
        <div class="flex items-center gap-3">
          <input
            :value="form.company_name_text_color || COMPANY_NAME_BRAND_DEFAULTS.text_color"
            type="color"
            class="h-10 w-14 cursor-pointer rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 p-1"
            @input="form.company_name_text_color = $event.target.value"
          />
          <input
            v-model="form.company_name_text_color"
            type="text"
            dir="ltr"
            maxlength="7"
            class="form-input text-body font-mono uppercase"
            :placeholder="COMPANY_NAME_BRAND_DEFAULTS.text_color"
          />
        </div>
        <p v-if="form.errors.company_name_text_color" class="form-error">{{ form.errors.company_name_text_color }}</p>
      </div>
    </div>

    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-white p-4">
      <p class="mb-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ t('companyInfo.form.companyNameBrandPreviewLabel') }}</p>
      <div class="flex flex-wrap gap-6">
        <div class="min-w-[16rem]">
          <p class="mb-2 text-xs text-muted muted-color">{{ t('companyInfo.form.companyNameBrandPreviewLtr') }}</p>
          <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 px-4 py-3">
            <div class="px-nav-brand" dir="ltr">
              <img v-if="previewLogo" :src="previewLogo" alt="" class="px-nav-logo" />
              <span class="px-nav-brand-names" :style="previewStyle">
                <span class="px-nav-brand-name-ar" dir="rtl" :style="previewArFontStyle">{{ previewNameAr }}</span>
                <span class="px-nav-brand-name-en" dir="ltr" :style="previewEnFontStyle">{{ previewNameEn }}</span>
              </span>
            </div>
          </div>
        </div>
        <div class="min-w-[16rem]">
          <p class="mb-2 text-xs text-muted muted-color">{{ t('companyInfo.form.companyNameBrandPreviewRtl') }}</p>
          <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 px-4 py-3">
            <div class="px-nav-brand" dir="rtl">
              <img v-if="previewLogo" :src="previewLogo" alt="" class="px-nav-logo" />
              <span class="px-nav-brand-names" :style="previewStyle">
                <span class="px-nav-brand-name-ar" dir="rtl" :style="previewArFontStyle">{{ previewNameAr }}</span>
                <span class="px-nav-brand-name-en" dir="ltr" :style="previewEnFontStyle">{{ previewNameEn }}</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.px-nav-brand {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  min-width: 0;
}

.px-nav-logo {
  height: 3.25rem;
  width: auto;
  max-width: 12.5rem;
  object-fit: contain;
}

.px-nav-brand-names {
  display: flex;
  flex-direction: column;
  gap: 0.12rem;
  min-width: 0;
  color: var(--px-brand-color, #0b1f3a);
}

.px-nav-brand-name-ar {
  font-family: var(--px-brand-font-family-ar, 'Cairo', 'Tajawal', Arial, sans-serif);
  font-size: var(--px-brand-font-size-ar, 0.98rem);
  font-weight: var(--px-brand-font-weight, 800);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.px-nav-brand-name-en {
  font-family: var(--px-brand-font-family-en, 'Poppins', Arial, sans-serif);
  font-size: var(--px-brand-font-size-en, 0.74rem);
  font-weight: var(--px-brand-font-weight, 800);
  line-height: 1.2;
  letter-spacing: 0.02em;
  opacity: 0.9;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
