<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <section class="space-y-4">
    <h2 class="text-card-title text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">
      Homepage settings
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="form-label text-label">Hero highlight (AR)</label>
        <input v-model="form.hero_highlight_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Hero highlight (EN)</label>
        <input v-model="form.hero_highlight_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Primary CTA label (AR)</label>
        <input v-model="form.hero_primary_cta_text_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Primary CTA label (EN)</label>
        <input v-model="form.hero_primary_cta_text_en" type="text" class="form-input text-body" />
      </div>
      <div class="md:col-span-2">
        <label class="form-label text-label">Primary CTA URL</label>
        <input v-model="form.hero_primary_cta_url" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Secondary CTA label (AR)</label>
        <input v-model="form.hero_secondary_cta_text_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Secondary CTA label (EN)</label>
        <input v-model="form.hero_secondary_cta_text_en" type="text" class="form-input text-body" />
      </div>
      <div class="md:col-span-2">
        <label class="form-label text-label">Secondary CTA URL</label>
        <input v-model="form.hero_secondary_cta_url" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Products section title (AR)</label>
        <input v-model="form.products_section_title_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Products section title (EN)</label>
        <input v-model="form.products_section_title_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Homepage products limit</label>
        <input v-model="form.products_homepage_limit" type="number" min="0" max="100" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Industries section title (AR)</label>
        <input v-model="form.industries_section_title_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Industries section title (EN)</label>
        <input v-model="form.industries_section_title_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">About section title (AR)</label>
        <input v-model="form.about_section_title_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">About section title (EN)</label>
        <input v-model="form.about_section_title_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">About title highlight (AR)</label>
        <input v-model="form.about_highlight_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">About title highlight (EN)</label>
        <input v-model="form.about_highlight_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">About CTA label (AR)</label>
        <input v-model="form.about_cta_text_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">About CTA label (EN)</label>
        <input v-model="form.about_cta_text_en" type="text" class="form-input text-body" />
      </div>
      <div class="md:col-span-2">
        <label class="form-label text-label">About CTA URL</label>
        <input v-model="form.about_cta_url" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Gallery section title (AR)</label>
        <input v-model="form.gallery_section_title_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Gallery section title (EN)</label>
        <input v-model="form.gallery_section_title_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Contact section title (AR)</label>
        <input v-model="form.contact_section_title_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Contact section title (EN)</label>
        <input v-model="form.contact_section_title_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Contact section subtitle (AR)</label>
        <textarea v-model="form.contact_section_subtitle_ar" rows="2" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Contact section subtitle (EN)</label>
        <textarea v-model="form.contact_section_subtitle_en" rows="2" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Footer description (AR)</label>
        <textarea v-model="form.footer_description_ar" rows="3" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Footer description (EN)</label>
        <textarea v-model="form.footer_description_en" rows="3" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter title (AR)</label>
        <input v-model="form.footer_newsletter_title_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter title (EN)</label>
        <input v-model="form.footer_newsletter_title_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter description (AR)</label>
        <textarea v-model="form.footer_newsletter_description_ar" rows="2" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter description (EN)</label>
        <textarea v-model="form.footer_newsletter_description_en" rows="2" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter button (AR)</label>
        <input v-model="form.footer_newsletter_button_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter button (EN)</label>
        <input v-model="form.footer_newsletter_button_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter placeholder (AR)</label>
        <input v-model="form.footer_newsletter_placeholder_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Newsletter placeholder (EN)</label>
        <input v-model="form.footer_newsletter_placeholder_en" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Copyright (AR)</label>
        <input v-model="form.footer_copyright_ar" type="text" class="form-input text-body" />
      </div>
      <div>
        <label class="form-label text-label">Copyright (EN)</label>
        <input v-model="form.footer_copyright_en" type="text" class="form-input text-body" />
      </div>
    </div>
  </section>
</template>
