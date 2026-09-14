import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const homepageDefaults = {
  hero_highlight_ar: '',
  hero_highlight_en: '',
  hero_primary_cta_text_ar: '',
  hero_primary_cta_text_en: '',
  hero_primary_cta_url: '',
  hero_secondary_cta_text_ar: '',
  hero_secondary_cta_text_en: '',
  hero_secondary_cta_url: '',
  products_section_title_ar: '',
  products_section_title_en: '',
  products_homepage_limit: 8,
  industries_section_title_ar: '',
  industries_section_title_en: '',
  about_section_title_ar: '',
  about_section_title_en: '',
  about_highlight_ar: '',
  about_highlight_en: '',
  about_cta_text_ar: '',
  about_cta_text_en: '',
  about_cta_url: '',
  gallery_section_title_ar: '',
  gallery_section_title_en: '',
  contact_section_title_ar: '',
  contact_section_title_en: '',
  contact_section_subtitle_ar: '',
  contact_section_subtitle_en: '',
  footer_description_ar: '',
  footer_description_en: '',
  footer_newsletter_title_ar: '',
  footer_newsletter_title_en: '',
  footer_newsletter_description_ar: '',
  footer_newsletter_description_en: '',
  footer_newsletter_button_ar: '',
  footer_newsletter_button_en: '',
  footer_newsletter_placeholder_ar: '',
  footer_newsletter_placeholder_en: '',
  footer_copyright_ar: '',
  footer_copyright_en: '',
}

const defaultFormValues = (companyInfo) => ({
  name_ar: companyInfo.name_ar || '',
  name_en: companyInfo.name_en || '',
  company_name_text_color: companyInfo.company_name_text_color || '',
  company_name_font_family_ar: companyInfo.company_name_font_family_ar || '',
  company_name_font_family_en: companyInfo.company_name_font_family_en || '',
  company_name_font_size_ar: companyInfo.company_name_font_size_ar ?? '',
  company_name_font_size_en: companyInfo.company_name_font_size_en ?? '',
  company_name_font_weight: companyInfo.company_name_font_weight ? String(companyInfo.company_name_font_weight) : '',
  phone: companyInfo.phone || '',
  email: companyInfo.email || '',
  hero_title_ar: companyInfo.hero_title_ar || '',
  hero_title_en: companyInfo.hero_title_en || '',
  hero_description_ar: companyInfo.hero_description_ar || '',
  hero_description_en: companyInfo.hero_description_en || '',
  about_ar: companyInfo.about_ar || '',
  about_en: companyInfo.about_en || '',
  vision_ar: companyInfo.vision_ar || '',
  vision_en: companyInfo.vision_en || '',
  mission_ar: companyInfo.mission_ar || '',
  mission_en: companyInfo.mission_en || '',
  address_ar: companyInfo.address_ar || '',
  address_en: companyInfo.address_en || '',
  google_maps_embed_url: companyInfo.google_maps_embed_url || '',
  website: companyInfo.website || '',
  facebook: companyInfo.facebook || '',
  instagram: companyInfo.instagram || '',
  linkedin: companyInfo.linkedin || '',
  x_twitter: companyInfo.x_twitter || '',
  youtube: companyInfo.youtube || '',
  tiktok: companyInfo.tiktok || '',
  snapchat: companyInfo.snapchat || '',
  whatsapp: companyInfo.whatsapp || '',
  ...Object.fromEntries(Object.entries(homepageDefaults).map(([key, fallback]) => [
    key,
    companyInfo[key] ?? fallback,
  ])),
  logo: null,
  about_image: null,
})

export function useCompanyInfo(companyInfo) {
  const form = useForm(defaultFormValues(companyInfo))

  const logoInput = ref(null)
  const logoFileName = ref(null)
  const logoPreview = ref(companyInfo.attachment?.asset_path || null)
  const aboutImageInput = ref(null)
  const aboutImageFileName = ref(null)
  const aboutImagePreview = ref(companyInfo.about_attachment?.asset_path || companyInfo.about_image || null)

  const handleLogoChange = (event) => {
    const file = event.target.files[0] || null
    form.logo = file
    logoFileName.value = file?.name || null
    logoPreview.value = file ? URL.createObjectURL(file) : (companyInfo.attachment?.asset_path || null)
  }

  const handleAboutImageChange = (event) => {
    const file = event.target.files[0] || null
    form.about_image = file
    aboutImageFileName.value = file?.name || null
    aboutImagePreview.value = file
      ? URL.createObjectURL(file)
      : (companyInfo.about_attachment?.asset_path || companyInfo.about_image || null)
  }

  const updateCompanyInfo = (options = {}) =>
    form.transform((data) => ({ ...data, _method: 'put' })).post(route('company-info.update'), {
      preserveScroll: true,
      onSuccess: () => {
        form.logo = null
        form.about_image = null
        logoFileName.value = null
        aboutImageFileName.value = null
        if (logoInput.value) logoInput.value.value = ''
        if (aboutImageInput.value) aboutImageInput.value.value = ''
      },
      ...options,
    })

  return {
    form,
    logoInput,
    logoFileName,
    logoPreview,
    aboutImageInput,
    aboutImageFileName,
    aboutImagePreview,
    handleLogoChange,
    handleAboutImageChange,
    updateCompanyInfo,
  }
}
