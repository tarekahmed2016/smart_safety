<script setup>
import { computed, ref, watch } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { formatHomepageTemplate, resolveHomepageField, resolveHomepageScalar } from '../../Composables/useHomepageContent.js'
import { useHomepageSections } from '../../Composables/useHomepageSections.js'
import { plainTextFromHtml } from '../../Composables/useRichText.js'
import RichTextContent from '../../Components/Common/RichTextContent.vue'
import PlastexLineIcon from '../../Components/Public/PlastexLineIcon.vue'
import ProductsCarousel from '../../Components/Public/ProductsCarousel.vue'

const { t, locale } = useI18n()
const page = usePage()

const companyInfo = computed(() => page.props.companyInfo || {})
const homepageSections = computed(() => page.props.homepageSections || [])
const { normalizedSections } = useHomepageSections(homepageSections)
const heroSlides = computed(() => page.props.heroSlides || [])
const products = computed(() => page.props.products || [])
const projects = computed(() => page.props.projects || [])
const services = computed(() => page.props.services || [])
const featureHighlights = computed(() => page.props.featureHighlights || [])
const industries = computed(() => page.props.industries || [])
const customManufacturing = computed(() => page.props.customManufacturing || null)
const stats = computed(() => page.props.stats || [])
const businessCta = computed(() => page.props.businessCta || null)
const menuPages = computed(() => page.props.menuPages || [])

const companyName = computed(() =>
  resolveBilingualField(companyInfo.value, 'name', locale.value) || t('public.home.defaultCompanyName')
)

const heroTitle = computed(() =>
  resolveBilingualField(companyInfo.value, 'hero_title', locale.value)
    || `${t('public.home.hero.titleLine1')}\n${t('public.home.hero.titleLine2')}`
)

const heroHighlight = computed(() =>
  resolveHomepageField(companyInfo.value, 'hero_highlight', locale.value, t('public.home.hero.highlight'))
)

const heroPrimaryCtaLabel = computed(() =>
  resolveHomepageField(companyInfo.value, 'hero_primary_cta_text', locale.value, t('public.home.hero.ctaQuote'))
)
const heroPrimaryCtaUrl = computed(() =>
  resolveHomepageScalar(companyInfo.value, 'hero_primary_cta_url', '#contact')
)
const heroSecondaryCtaLabel = computed(() =>
  resolveHomepageField(companyInfo.value, 'hero_secondary_cta_text', locale.value, t('public.home.hero.ctaProducts'))
)
const heroSecondaryCtaUrl = computed(() =>
  resolveHomepageScalar(companyInfo.value, 'hero_secondary_cta_url', '#products')
)

const heroTitleLines = computed(() => {
  return heroTitle.value
    .split(/\n+/)
    .map((line) => line.trim())
    .filter(Boolean)
    .map((line) => {
      const highlight = heroHighlight.value
      if (highlight && line.includes(highlight)) {
        const index = line.indexOf(highlight)
        return [
          { text: line.slice(0, index), highlight: false },
          { text: highlight, highlight: true },
          { text: line.slice(index + highlight.length), highlight: false },
        ].filter((part) => part.text)
      }

      return [{ text: line, highlight: false }]
    })
})

const heroDescriptionHtml = computed(() =>
  resolveBilingualField(companyInfo.value, 'hero_description', locale.value)
)

const heroDescriptionFallback = computed(() => t('public.home.hero.supporting'))

const heroBackground = computed(() => {
  const slideWithImage = heroSlides.value.find((slide) => slide.image)
  return slideWithImage?.image || '/images/plastex/hero.jpg'
})

const aboutText = computed(() => resolveBilingualField(companyInfo.value, 'about', locale.value))
const aboutFallback = computed(() => t('public.home.about.descriptionWithCompany', { company: companyName.value }))
const aboutImage = computed(() =>
  companyInfo.value.about_image
    || customManufacturing.value?.image
    || heroBackground.value
    || products.value.find((item) => item.image)?.image
    || null
)

const aboutMoreHref = computed(() => {
  const configuredUrl = resolveHomepageScalar(companyInfo.value, 'about_cta_url')
  if (configuredUrl) {
    return configuredUrl
  }

  const aboutPage = menuPages.value.find((item) => item.slug === 'about')
  return aboutPage ? route('public.page.show', { slug: aboutPage.slug }) : '#contact'
})
const aboutMoreLabel = computed(() =>
  resolveHomepageField(companyInfo.value, 'about_cta_text', locale.value, t('public.home.about.more'))
)

const manufacturingTitle = computed(() =>
  customManufacturing.value
    ? resolveBilingualField(customManufacturing.value, 'title', locale.value)
    : t('public.home.manufacturing.title')
)
const manufacturingDescription = computed(() =>
  customManufacturing.value
    ? resolveBilingualField(customManufacturing.value, 'description', locale.value)
    : t('public.home.manufacturing.description')
)
const manufacturingCta = computed(() =>
  customManufacturing.value
    ? resolveBilingualField(customManufacturing.value, 'cta_text', locale.value) || t('public.home.manufacturing.cta')
    : t('public.home.manufacturing.cta')
)
const manufacturingUrl = computed(() => customManufacturing.value?.cta_url || '#contact')
const manufacturingImage = computed(() => customManufacturing.value?.image || aboutImage.value)

const displayFeatures = computed(() => featureHighlights.value.map((item) => ({
  icon: item.icon || 'quality',
  title: resolveBilingualField(item, 'title', locale.value),
  text: plainTextFromHtml(resolveBilingualField(item, 'description', locale.value)),
  image: item.image || null,
})))

const displayServices = computed(() => services.value.map((item) => ({
  icon: 'quality',
  title: resolveBilingualField(item, 'name', locale.value),
  text: plainTextFromHtml(resolveBilingualField(item, 'description', locale.value))
    || t('public.home.services.noDescription'),
  image: item.image || null,
})))

const displayIndustries = computed(() => industries.value.map((item) => ({
  icon: item.icon || 'industry',
  title: resolveBilingualField(item, 'title', locale.value),
  image: item.image || null,
})))

const displayStats = computed(() => stats.value.map((item) => ({
  value: resolveBilingualField(item, 'title', locale.value),
  label: plainTextFromHtml(resolveBilingualField(item, 'description', locale.value)),
})))

const productName = (product) => resolveBilingualField(product, 'name', locale.value)
const productExcerpt = (product) =>
  resolveBilingualField(product, 'excerpt', locale.value)
    || t('public.home.products.noDescription')

const resolveSectionTitle = (section, companyField, fallbackKey) => {
  const fromSection = resolveBilingualField(section, 'title', locale.value)
  if (fromSection) {
    return fromSection
  }

  if (companyField) {
    return resolveHomepageField(companyInfo.value, companyField, locale.value, t(fallbackKey))
  }

  return t(fallbackKey)
}

const galleryItemsForSection = (section) => {
  const maxItems = Number(section?.settings?.max_items || 8)
  const items = []

  products.value.forEach((product) => {
    if (product.image) {
      items.push({ src: product.image, alt: productName(product) })
    }
  })

  projects.value.forEach((project) => {
    if (project.image) {
      items.push({
        src: project.image,
        alt: resolveBilingualField(project, 'name', locale.value) || t('public.home.gallery.imageAlt'),
      })
    }
  })

  heroSlides.value.forEach((slide, index) => {
    if (slide.image) {
      items.push({
        src: slide.image,
        alt: resolveBilingualField(slide, 'title', locale.value) || t('public.home.gallery.slideAlt', { number: index + 1 }),
      })
    }
  })

  const unique = []
  const seen = new Set()
  items.forEach((item) => {
    if (!seen.has(item.src)) {
      seen.add(item.src)
      unique.push(item)
    }
  })

  return unique.slice(0, maxItems)
}

const contactCtaTitle = computed(() =>
  businessCta.value
    ? resolveBilingualField(businessCta.value, 'title', locale.value) || t('public.home.contactCta.title')
    : t('public.home.contactCta.title')
)
const contactCtaText = computed(() =>
  businessCta.value
    ? plainTextFromHtml(resolveBilingualField(businessCta.value, 'description', locale.value)) || t('public.home.contactCta.subtitle')
    : t('public.home.contactCta.subtitle')
)
const contactCtaLabel = computed(() =>
  businessCta.value
    ? resolveBilingualField(businessCta.value, 'cta_text', locale.value) || t('public.home.contactCta.button')
    : t('public.home.contactCta.button')
)
const whatsappUrl = computed(() => companyInfo.value.whatsapp || null)
const contactCtaUrl = computed(() => {
  if (businessCta.value?.cta_url) return businessCta.value.cta_url
  if (whatsappUrl.value) return whatsappUrl.value
  return '#contact-form'
})
const contactCtaBackground = computed(() => businessCta.value?.image || heroBackground.value)

const contactForm = useForm({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})
const contactFormSuccess = ref(false)

watch(() => page.props.flash, (flash) => {
  if (flash?.success === 'contact_message_sent') {
    contactFormSuccess.value = true
  }
}, { immediate: true, deep: true })

const submitContactForm = () => {
  contactFormSuccess.value = false

  contactForm.post(route('contact.store'), {
    preserveScroll: true,
    onSuccess: () => {
      contactForm.reset()
      contactFormSuccess.value = true
    },
  })
}
</script>

<template>
  <template v-for="section in normalizedSections" :key="section.key">
    <section v-if="section.type === 'hero'" id="home" class="px-hero">
      <div
        class="px-hero-bg has-image"
        :style="{ backgroundImage: `url('${heroBackground}')` }"
        role="img"
        :aria-label="companyName"
      ></div>
      <div class="px-hero-overlay"></div>
      <div class="px-hero-shell">
        <div class="px-hero-content">
          <h1 class="px-hero-title">
            <span v-for="(line, lineIndex) in heroTitleLines" :key="lineIndex" class="px-hero-line">
              <template v-for="(part, partIndex) in line" :key="`${lineIndex}-${partIndex}`">
                <span :class="{ 'px-hero-highlight': part.highlight }">{{ part.text }}</span>
              </template>
            </span>
          </h1>
          <RichTextContent
            v-if="heroDescriptionHtml"
            :content="heroDescriptionHtml"
            tag="div"
            class="px-hero-copy"
          />
          <p v-else class="px-hero-copy">{{ heroDescriptionFallback }}</p>
          <div class="px-hero-actions">
            <a :href="heroPrimaryCtaUrl" class="px-btn px-btn-green">
              <svg class="px-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M22 2L11 13" />
                <path d="M22 2l-7 20-4-9-9-4 20-7z" />
              </svg>
              {{ heroPrimaryCtaLabel }}
            </a>
            <a :href="heroSecondaryCtaUrl" class="px-btn px-btn-outline">
              <svg class="px-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="3" width="7" height="7" rx="1" />
                <rect x="3" y="14" width="7" height="7" rx="1" />
                <rect x="14" y="14" width="7" height="7" rx="1" />
              </svg>
              {{ heroSecondaryCtaLabel }}
            </a>
          </div>
        </div>
      </div>
    </section>

    <section
      v-else-if="section.type === 'features' && displayFeatures.length"
      class="px-features"
      :aria-label="t('public.home.features.regionLabel')"
    >
      <div class="px-container px-features-grid">
        <article v-for="feature in displayFeatures" :key="feature.title" class="px-feature">
          <div class="px-feature-icon" aria-hidden="true">
            <img v-if="feature.image" :src="feature.image" :alt="''" />
            <PlastexLineIcon v-else :name="feature.icon" />
          </div>
          <h2>{{ feature.title }}</h2>
          <p>{{ feature.text }}</p>
        </article>
      </div>
    </section>

    <section v-else-if="section.type === 'products'" id="products" class="px-products">
      <div class="px-container">
        <div class="px-section-header">
          <h2>{{ resolveSectionTitle(section, 'products_section_title', 'public.home.products.title') }}</h2>
        </div>

        <ProductsCarousel v-if="products.length" :products="products" />
        <p v-else class="px-empty">{{ t('public.home.products.empty') }}</p>

        <div class="px-section-footer">
          <Link :href="route('public.products.index')" class="px-text-link px-text-link-lg">
            {{ t('public.home.products.viewAll') }}
          </Link>
        </div>
      </div>
    </section>

    <section
      v-else-if="section.type === 'services' && displayServices.length"
      id="services"
      class="px-features"
      :aria-label="t('public.home.services.title')"
    >
      <div class="px-container">
        <div class="px-section-header">
          <h2>{{ resolveSectionTitle(section, null, 'public.home.services.title') }}</h2>
        </div>
      </div>
      <div class="px-container px-features-grid">
        <article v-for="service in displayServices" :key="service.title" class="px-feature">
          <div class="px-feature-icon" aria-hidden="true">
            <img v-if="service.image" :src="service.image" :alt="''" />
            <PlastexLineIcon v-else :name="service.icon" />
          </div>
          <h2>{{ service.title }}</h2>
          <p>{{ service.text }}</p>
        </article>
      </div>
    </section>

    <section v-else-if="section.type === 'split'" id="custom-manufacturing" class="px-split">
      <div class="px-split-custom">
        <div class="px-split-copy">
          <div class="px-split-visual" :class="{ 'has-image': Boolean(manufacturingImage) }">
            <img
              v-if="manufacturingImage"
              :src="manufacturingImage"
              :alt="manufacturingTitle"
              class="px-split-visual-image"
            />
          </div>
          <h2>{{ manufacturingTitle }}</h2>
          <RichTextContent
            v-if="customManufacturing && resolveBilingualField(customManufacturing, 'description', locale)"
            :content="resolveBilingualField(customManufacturing, 'description', locale)"
            tag="div"
          />
          <p v-else>{{ manufacturingDescription }}</p>
          <a :href="manufacturingUrl" class="px-btn px-btn-green">{{ manufacturingCta }}</a>
        </div>
      </div>
      <div
        class="px-split-industries"
        :class="{ 'has-image': Boolean(heroBackground) }"
        :style="heroBackground ? { backgroundImage: `url('${heroBackground}')` } : undefined"
      >
        <div v-if="displayIndustries.length" class="px-split-industries-inner">
          <h2>{{ resolveSectionTitle(section.industries, 'industries_section_title', 'public.home.industries.title') }}</h2>
          <ul class="px-industry-grid">
            <li v-for="industry in displayIndustries" :key="industry.title">
              <span class="px-industry-icon" aria-hidden="true">
                <img v-if="industry.image" :src="industry.image" :alt="''" />
                <PlastexLineIcon v-else :name="industry.icon" />
              </span>
              <span>{{ industry.title }}</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <section v-else-if="section.type === 'custom_manufacturing'" id="custom-manufacturing" class="px-split">
      <div class="px-split-custom">
        <div class="px-split-copy">
          <div class="px-split-visual" :class="{ 'has-image': Boolean(manufacturingImage) }">
            <img
              v-if="manufacturingImage"
              :src="manufacturingImage"
              :alt="manufacturingTitle"
              class="px-split-visual-image"
            />
          </div>
          <h2>{{ manufacturingTitle }}</h2>
          <RichTextContent
            v-if="customManufacturing && resolveBilingualField(customManufacturing, 'description', locale)"
            :content="resolveBilingualField(customManufacturing, 'description', locale)"
            tag="div"
          />
          <p v-else>{{ manufacturingDescription }}</p>
          <a :href="manufacturingUrl" class="px-btn px-btn-green">{{ manufacturingCta }}</a>
        </div>
      </div>
    </section>

    <section
      v-else-if="section.type === 'industries'"
      id="industries"
      class="px-split-industries px-split-industries--standalone"
      :class="{ 'has-image': Boolean(heroBackground) }"
      :style="heroBackground ? { backgroundImage: `url('${heroBackground}')` } : undefined"
    >
      <div v-if="displayIndustries.length" class="px-split-industries-inner px-container">
        <h2>{{ resolveSectionTitle(section, 'industries_section_title', 'public.home.industries.title') }}</h2>
        <ul class="px-industry-grid">
          <li v-for="industry in displayIndustries" :key="industry.title">
            <span class="px-industry-icon" aria-hidden="true">
              <img v-if="industry.image" :src="industry.image" :alt="''" />
              <PlastexLineIcon v-else :name="industry.icon" />
            </span>
            <span>{{ industry.title }}</span>
          </li>
        </ul>
      </div>
    </section>

    <section v-else-if="section.type === 'about'" id="about" class="px-about">
      <div class="px-container px-about-grid">
        <div class="px-about-copy">
          <h2>{{ resolveSectionTitle(section, 'about_section_title', 'public.home.about.factoryTitle') }}</h2>
          <RichTextContent
            v-if="aboutText"
            :content="aboutText"
            tag="div"
            class="px-about-text"
          />
          <p v-else class="px-about-text">{{ aboutFallback }}</p>

          <div v-if="displayStats.length" class="px-stats-grid">
            <article v-for="stat in displayStats" :key="stat.label" class="px-stat">
              <strong>{{ stat.value }}</strong>
              <span>{{ stat.label }}</span>
            </article>
          </div>

          <a :href="aboutMoreHref" class="px-btn px-btn-blue">{{ aboutMoreLabel }}</a>
        </div>
        <div class="px-about-media">
          <img
            v-if="aboutImage"
            :src="aboutImage"
            :alt="t('public.home.about.imageAlt', { company: companyName })"
          />
          <div v-else class="px-media-fallback px-media-fallback-tall" :aria-hidden="true"></div>
        </div>
      </div>
    </section>

    <section v-else-if="section.type === 'gallery'" id="gallery" class="px-gallery">
      <div class="px-container">
        <div class="px-section-header">
          <h2>{{ resolveSectionTitle(section, 'gallery_section_title', 'public.home.gallery.title') }}</h2>
        </div>
        <div v-if="galleryItemsForSection(section).length" class="px-gallery-grid">
          <figure v-for="item in galleryItemsForSection(section)" :key="item.src">
            <img :src="item.src" :alt="item.alt" />
          </figure>
        </div>
        <p v-else class="px-empty">{{ t('public.home.gallery.empty') }}</p>
      </div>
    </section>

    <section v-else-if="section.type === 'contact_cta'" class="px-contact-cta">
      <div
        class="px-contact-cta-bg"
        :class="{ 'has-image': Boolean(contactCtaBackground) }"
        :style="contactCtaBackground ? { backgroundImage: `url('${contactCtaBackground}')` } : undefined"
      ></div>
      <div class="px-container px-contact-cta-inner">
        <h2>{{ contactCtaTitle }}</h2>
        <p>{{ contactCtaText }}</p>
        <a :href="contactCtaUrl" class="px-btn px-btn-green">{{ contactCtaLabel }}</a>
      </div>
    </section>

    <section v-else-if="section.type === 'contact_form'" id="contact" class="px-contact">
      <div class="px-container px-contact-grid">
        <div>
          <h2>{{ resolveSectionTitle(section, 'contact_section_title', 'public.home.contact.title') }}</h2>
          <p>{{ resolveHomepageField(companyInfo, 'contact_section_subtitle', locale, t('public.home.contact.subtitle')) }}</p>
          <ul class="px-contact-details">
            <li v-if="companyInfo.phone">
              <span>{{ t('public.home.contact.phone') }}</span>
              <a :href="`tel:${companyInfo.phone}`" dir="ltr">{{ companyInfo.phone }}</a>
            </li>
            <li v-if="companyInfo.email">
              <span>{{ t('public.home.contact.email') }}</span>
              <a :href="`mailto:${companyInfo.email}`">{{ companyInfo.email }}</a>
            </li>
            <li v-if="resolveBilingualField(companyInfo, 'address', locale)">
              <span>{{ t('public.home.contact.address') }}</span>
              <p>{{ resolveBilingualField(companyInfo, 'address', locale) }}</p>
            </li>
          </ul>
        </div>

        <form id="contact-form" class="px-contact-form" @submit.prevent="submitContactForm">
          <div>
            <label for="contact-name">{{ t('public.home.contact.formName') }} <span aria-hidden="true">*</span></label>
            <input id="contact-name" v-model="contactForm.name" type="text" required :placeholder="t('public.home.contact.formNamePlaceholder')" />
            <p v-if="contactForm.errors.name" class="px-form-error">{{ contactForm.errors.name }}</p>
          </div>
          <div class="px-form-row">
            <div>
              <label for="contact-email">{{ t('public.home.contact.formEmail') }}</label>
              <input id="contact-email" v-model="contactForm.email" type="email" :placeholder="t('public.home.contact.formEmailPlaceholder')" />
              <p v-if="contactForm.errors.email" class="px-form-error">{{ contactForm.errors.email }}</p>
            </div>
            <div>
              <label for="contact-phone">{{ t('public.home.contact.formPhone') }}</label>
              <input id="contact-phone" v-model="contactForm.phone" type="tel" :placeholder="t('public.home.contact.formPhonePlaceholder')" />
              <p v-if="contactForm.errors.phone" class="px-form-error">{{ contactForm.errors.phone }}</p>
            </div>
          </div>
          <div>
            <label for="contact-subject">{{ t('public.home.contact.formSubject') }}</label>
            <input id="contact-subject" v-model="contactForm.subject" type="text" :placeholder="t('public.home.contact.formSubjectPlaceholder')" />
            <p v-if="contactForm.errors.subject" class="px-form-error">{{ contactForm.errors.subject }}</p>
          </div>
          <div>
            <label for="contact-message">{{ t('public.home.contact.formMessage') }} <span aria-hidden="true">*</span></label>
            <textarea id="contact-message" v-model="contactForm.message" rows="5" required :placeholder="t('public.home.contact.formMessagePlaceholder')"></textarea>
            <p v-if="contactForm.errors.message" class="px-form-error">{{ contactForm.errors.message }}</p>
          </div>
          <p v-if="contactForm.errors.contact_method" class="px-form-error">{{ contactForm.errors.contact_method }}</p>
          <button type="submit" class="px-btn px-btn-green" :disabled="contactForm.processing">
            {{ contactForm.processing ? t('public.home.contact.formSending') : t('public.home.contact.formSend') }}
          </button>
          <p v-if="contactFormSuccess" class="px-form-success">{{ t('public.home.contact.messageSentSuccess') }}</p>
        </form>
      </div>
    </section>
  </template>
</template>
