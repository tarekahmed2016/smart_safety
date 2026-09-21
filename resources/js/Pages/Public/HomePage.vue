<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import {
  PUBLIC_CONTACT_OPEN_EVENT,
  collectPublicContactFormErrors,
  scrollToPublicContact,
} from '../../Composables/usePublicContactForm.js'
import { resolveHomepageField, resolveHomepageScalar } from '../../Composables/useHomepageContent.js'
import { useHomepageSections } from '../../Composables/useHomepageSections.js'
import { plainTextFromHtml } from '../../Composables/useRichText.js'
import RichTextContent from '../../Components/Common/RichTextContent.vue'
import SmartSafetyIcon from '../../Components/Public/SmartSafetyIcon.vue'
import EmptyMediaPlaceholder from '../../Components/Public/EmptyMediaPlaceholder.vue'
import SectionContainer from '../../Components/Public/SectionContainer.vue'
import SectionHeading from '../../Components/Public/SectionHeading.vue'
import ProductsCarousel from '../../Components/Public/ProductsCarousel.vue'
import TeamMembersCarousel from '../../Components/Public/TeamMembersCarousel.vue'
import ClientsPartnersCarousel from '../../Components/Public/ClientsPartnersCarousel.vue'

const { t, locale } = useI18n()
const page = usePage()

const companyInfo = computed(() => page.props.companyInfo || {})
const homepageSections = computed(() => page.props.homepageSections || [])
const { normalizedSections } = useHomepageSections(homepageSections)
const heroSlides = computed(() => page.props.heroSlides || [])
const products = computed(() => page.props.products || [])
const projects = computed(() => page.props.projects || [])
const services = computed(() => page.props.services || [])
const teamMembers = computed(() => page.props.teamMembers || [])
const clientsPartners = computed(() => page.props.clientsPartners || [])
const featureHighlights = computed(() => page.props.featureHighlights || [])
const industries = computed(() => page.props.industries || [])
const customManufacturing = computed(() => page.props.customManufacturing || null)
const stats = computed(() => page.props.stats || [])
const aboutHighlights = computed(() => page.props.aboutHighlights || [])
const whyUsHighlights = computed(() => page.props.whyUsHighlights || [])
const businessCta = computed(() => page.props.businessCta || null)
const menuPages = computed(() => page.props.menuPages || [])
const companyGoals = computed(() => page.props.companyGoals || [])

const companyName = computed(() =>
  resolveBilingualField(companyInfo.value, 'name', locale.value) || t('public.home.defaultCompanyName')
)

const heroTitle = computed(() =>
  resolveBilingualField(companyInfo.value, 'hero_title', locale.value)
    || companyName.value
)

const heroHighlight = computed(() =>
  resolveHomepageField(companyInfo.value, 'hero_highlight', locale.value, '')
)

const heroEyebrow = computed(() =>
  resolveHomepageField(companyInfo.value, 'hero_eyebrow', locale.value, '')
)

const heroPrimaryCtaLabel = computed(() =>
  resolveHomepageField(companyInfo.value, 'hero_primary_cta_text', locale.value, t('public.home.hero.ctaQuote'))
)
const heroPrimaryCtaUrl = computed(() =>
  resolveHomepageScalar(companyInfo.value, 'hero_primary_cta_url', '#contact')
)
const heroSecondaryCtaLabel = computed(() =>
  resolveHomepageField(companyInfo.value, 'hero_secondary_cta_text', locale.value, '')
)
const heroSecondaryCtaUrl = computed(() =>
  resolveHomepageScalar(companyInfo.value, 'hero_secondary_cta_url', '#contact')
)
const hasHeroSecondaryCta = computed(() => Boolean(heroSecondaryCtaLabel.value))

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

const heroBackground = computed(() => {
  const slideWithImage = heroSlides.value.find((slide) => slide.image)
  return slideWithImage?.image || ''
})

const heroMobileImage = computed(() => {
  const slideWithImage = heroSlides.value.find((slide) => slide.image)
  return slideWithImage?.mobile_image || null
})

const aboutText = computed(() => resolveBilingualField(companyInfo.value, 'about', locale.value))
const aboutImage = computed(() => companyInfo.value.about_image || null)

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

const aboutHighlight = (section) =>
  resolveBilingualField(section?.settings || {}, 'highlight', locale.value)

const aboutTitleParts = (section) => highlightTitleParts(
  resolveSectionTitle(section, 'public.home.about.title'),
  aboutHighlight(section),
)

const highlightTitleParts = (title, highlight) => {
  if (highlight && title.includes(highlight)) {
    const index = title.indexOf(highlight)

    return [
      { text: title.slice(0, index), highlight: false },
      { text: highlight, highlight: true },
      { text: title.slice(index + highlight.length), highlight: false },
    ].filter((part) => part.text)
  }

  return [{ text: title, highlight: false }]
}

const servicesTitleParts = (section) => highlightTitleParts(
  resolveSectionTitle(section, 'public.home.services.title'),
  resolveBilingualField(section?.settings || {}, 'highlight', locale.value) || t('public.home.services.highlight'),
)

const servicesSubtitle = (section) =>
  resolveBilingualField(section?.settings || {}, 'subtitle', locale.value)

const contactSubtitle = (section) =>
  resolveBilingualField(section?.settings || {}, 'subtitle', locale.value)

const serviceIconFor = (item, index) => {
  const configured = item.icon
  if (configured) {
    return configured
  }

  const icons = ['shield', 'quality', 'settings']
  return icons[index % icons.length]
}

const manufacturingTitle = computed(() =>
  customManufacturing.value
    ? resolveBilingualField(customManufacturing.value, 'title', locale.value)
    : ''
)
const manufacturingDescription = computed(() =>
  customManufacturing.value
    ? resolveBilingualField(customManufacturing.value, 'description', locale.value)
    : ''
)
const manufacturingCta = computed(() =>
  customManufacturing.value
    ? resolveBilingualField(customManufacturing.value, 'cta_text', locale.value)
    : ''
)
const manufacturingUrl = computed(() => customManufacturing.value?.cta_url || '')
const hasManufacturingAction = computed(() => Boolean(manufacturingCta.value && manufacturingUrl.value))
const manufacturingImage = computed(() => customManufacturing.value?.image || null)
const hasCustomManufacturingContent = computed(() => Boolean(
  manufacturingTitle.value || manufacturingDescription.value || manufacturingImage.value
))

const displayFeatures = computed(() => featureHighlights.value.map((item) => ({
  icon: item.icon || 'quality',
  title: resolveBilingualField(item, 'title', locale.value),
  text: plainTextFromHtml(resolveBilingualField(item, 'description', locale.value)),
  image: item.image || null,
})))

const displayServices = computed(() => services.value.map((item, index) => ({
  key: `${item.name_ar || 'service'}-${item.name_en || index}`,
  icon: serviceIconFor(item, index),
  title: resolveBilingualField(item, 'name', locale.value),
  text: plainTextFromHtml(resolveBilingualField(item, 'description', locale.value)),
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
  icon: item.icon || null,
})))

const displayAboutHighlights = computed(() => aboutHighlights.value.map((item) => ({
  title: resolveBilingualField(item, 'title', locale.value),
  text: plainTextFromHtml(resolveBilingualField(item, 'description', locale.value)),
  icon: item.icon || 'quality',
})))

const hasAboutContent = computed(() => Boolean(
  aboutText.value
  || displayAboutHighlights.value.length
  || displayStats.value.length
  || aboutImage.value
))

const displayWhyUs = computed(() => whyUsHighlights.value.map((item) => ({
  title: resolveBilingualField(item, 'title', locale.value),
  text: plainTextFromHtml(resolveBilingualField(item, 'description', locale.value)),
  icon: item.icon || 'flag',
  image: item.image || null,
})))

const whyUsTitleParts = (section) => highlightTitleParts(
  resolveSectionHeadline(section, 'public.home.whyUs.headline'),
  resolveBilingualField(section?.settings || {}, 'highlight', locale.value) || t('public.home.whyUs.highlight'),
)

const productName = (product) => resolveBilingualField(product, 'name', locale.value)
const productExcerpt = (product) =>
  resolveBilingualField(product, 'excerpt', locale.value)

const displayGoals = computed(() => companyGoals.value
  .map((goal) => resolveBilingualField(goal, 'text', locale.value).trim())
  .filter(Boolean)
)

const resolveSectionHeadline = (section, fallbackKey = 'public.home.goals.headline') => {
  const fromSection = resolveBilingualField(section, 'headline', locale.value)
  if (fromSection) {
    return fromSection
  }

  const fromSettings = resolveBilingualField(section?.settings || {}, 'headline', locale.value)
  if (fromSettings) {
    return fromSettings
  }

  return t(fallbackKey)
}

const resolveVisionMissionCard = (type) => {
  const heading = t(`public.home.visionMission.${type}Heading`)
  const cmsHtml = resolveBilingualField(companyInfo.value, type, locale.value)
  const cmsText = plainTextFromHtml(cmsHtml).replace(/\s+/g, ' ').trim()
  const headingText = heading.replace(/\s+/g, ' ').trim()

  if (!cmsText) {
    return { heading, html: '', text: '' }
  }

  if (cmsText.startsWith(headingText)) {
    const remainder = cmsText.slice(headingText.length).replace(/^[\s،,.:;:-]+/, '').trim()

    return { heading, html: '', text: remainder }
  }

  return { heading, html: cmsHtml, text: cmsText }
}

const visionCard = computed(() => resolveVisionMissionCard('vision'))
const missionCard = computed(() => resolveVisionMissionCard('mission'))
const hasVisionMissionContent = computed(() => Boolean(
  visionCard.value.html || visionCard.value.text || missionCard.value.html || missionCard.value.text
))

const resolveSectionTitle = (section, fallbackKey) => {
  const fromSection = resolveBilingualField(section, 'title', locale.value)
  if (fromSection) {
    return fromSection
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
    ? resolveBilingualField(businessCta.value, 'title', locale.value)
    : ''
)
const contactCtaText = computed(() =>
  businessCta.value
    ? plainTextFromHtml(resolveBilingualField(businessCta.value, 'description', locale.value))
    : ''
)
const contactCtaLabel = computed(() =>
  businessCta.value
    ? resolveBilingualField(businessCta.value, 'cta_text', locale.value)
    : ''
)
const whatsappUrl = computed(() => companyInfo.value.whatsapp || null)
const contactCtaUrl = computed(() => {
  if (businessCta.value?.cta_url) return businessCta.value.cta_url
  if (businessCta.value) return ''
  if (whatsappUrl.value) return whatsappUrl.value
  return '#contact-form'
})
const hasContactCtaAction = computed(() => Boolean(contactCtaLabel.value && contactCtaUrl.value))
const hasContactCtaContent = computed(() => Boolean(
  contactCtaTitle.value || contactCtaText.value || hasContactCtaAction.value
))
const contactCtaBackground = computed(() => businessCta.value?.image || heroBackground.value)
const industriesBackground = computed(() => heroBackground.value || null)

const recaptchaEnabled = computed(() => Boolean(page.props.recaptchaEnabled))
const contactForm = useForm({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})
const contactFormSuccess = ref(false)
const inquiredProductName = ref('')

watch(() => page.props.flash, (flash) => {
  if (flash?.success === 'contact_message_sent') {
    contactFormSuccess.value = true
  }
}, { immediate: true, deep: true })

const submitContactForm = () => {
  contactFormSuccess.value = false
  contactForm.clearErrors()

  const clientErrors = collectPublicContactFormErrors(contactForm, {
    recaptchaEnabled: recaptchaEnabled.value,
  })

  if (Object.keys(clientErrors).length) {
    contactForm.setError(clientErrors)
    return
  }

  contactForm.post(route('contact.store'), {
    preserveScroll: true,
    onSuccess: () => {
      contactForm.reset()
      contactFormSuccess.value = true
    },
  })
}

const resetContactForm = () => {
  inquiredProductName.value = ''
  contactForm.name = ''
  contactForm.email = ''
  contactForm.phone = ''
  contactForm.subject = ''
  contactForm.message = ''
  contactForm.reset()
  contactForm.clearErrors()
  contactFormSuccess.value = false
}

const handlePublicContactOpen = (event) => {
  if (event.detail?.reset !== false) {
    resetContactForm()
  }

  nextTick(() => {
    scrollToPublicContact()
    const focusId = event.detail?.focusId || 'contact-name'
    document.getElementById(focusId)?.focus({ preventScroll: true })

    const hash = window.location.hash
    if (hash !== '#contact' && hash !== '#contact-form') {
      history.replaceState(null, '', `${window.location.pathname}${window.location.search}#contact`)
    }
  })
}

const inquireAboutProduct = (product) => {
  const name = resolveBilingualField(product || {}, 'name', locale.value)
  if (!name) {
    return
  }

  inquiredProductName.value = name
  contactForm.subject = t('public.home.products.inquireSubject', { name })
  contactForm.message = t('public.home.products.inquireMessage', { name })

  nextTick(() => {
    scrollToPublicContact()
    document.getElementById('contact-message')?.focus({ preventScroll: true })
  })
}

onMounted(() => {
  window.addEventListener(PUBLIC_CONTACT_OPEN_EVENT, handlePublicContactOpen)
})

onUnmounted(() => {
  window.removeEventListener(PUBLIC_CONTACT_OPEN_EVENT, handlePublicContactOpen)
})
</script>

<template>
  <div class="smart-safety-home">
  <template v-for="section in normalizedSections" :key="section.key">
    <section v-if="section.type === 'hero'" id="home" class="px-hero ss-hero">
      <div class="px-hero-bg" :class="{ 'has-image': Boolean(heroBackground) }">
        <picture v-if="heroBackground" class="px-hero-picture">
          <source
            v-if="heroMobileImage"
            media="(max-width: 767px)"
            :srcset="heroMobileImage"
          />
          <img
            :src="heroBackground"
            :alt="companyName"
            class="px-hero-picture-image"
          />
        </picture>
      </div>
      <div class="px-hero-overlay"></div>
      <div class="px-hero-shell">
        <div class="px-hero-content">
          <p v-if="heroEyebrow" class="px-hero-eyebrow">{{ heroEyebrow }}</p>
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
          <div v-if="heroPrimaryCtaLabel || hasHeroSecondaryCta" class="px-hero-actions">
            <a v-if="heroPrimaryCtaLabel" :href="heroPrimaryCtaUrl" class="px-btn px-btn-green">
              <SmartSafetyIcon name="send" />
              {{ heroPrimaryCtaLabel }}
            </a>
            <a v-if="hasHeroSecondaryCta" :href="heroSecondaryCtaUrl" class="px-btn px-btn-outline">
              <SmartSafetyIcon name="shield" />
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
            <SmartSafetyIcon v-else :name="feature.icon" />
          </div>
          <h2>{{ feature.title }}</h2>
          <p>{{ feature.text }}</p>
        </article>
      </div>
    </section>

    <section
      v-else-if="section.type === 'why_us' && displayWhyUs.length"
      id="why-us"
      class="px-why-us"
      :aria-label="t('public.home.whyUs.title')"
    >
      <div class="px-container">
        <div class="px-section-header">
          <p class="px-about-eyebrow">{{ resolveSectionTitle(section, 'public.home.whyUs.title') }}</p>
          <h2>
            <span
              v-for="(part, index) in whyUsTitleParts(section)"
              :key="`${part.text}-${index}`"
              :class="{ 'px-hero-highlight': part.highlight }"
            >{{ part.text }}</span>
          </h2>
        </div>

        <div class="px-why-us-grid">
          <article v-for="item in displayWhyUs" :key="item.title" class="px-why-card">
            <div class="px-why-card-icon" aria-hidden="true">
              <img v-if="item.image" :src="item.image" :alt="''" />
              <SmartSafetyIcon v-else :name="item.icon" />
            </div>
            <h3>{{ item.title }}</h3>
            <p>{{ item.text }}</p>
          </article>
        </div>
      </div>
    </section>

    <section v-else-if="section.type === 'products' && products.length" id="products" class="px-products">
      <SectionContainer>
        <SectionHeading :title="resolveSectionTitle(section, 'public.home.products.title')" />
        <ProductsCarousel :products="products" @inquire="inquireAboutProduct" />
        <div class="px-section-footer">
          <Link :href="route('public.products.index')" class="px-text-link px-text-link-lg">
            {{ t('public.home.products.viewAll') }}
          </Link>
        </div>
      </SectionContainer>
    </section>

    <section
      v-else-if="section.type === 'services' && displayServices.length"
      id="services"
      class="px-services"
      :aria-label="t('public.home.services.title')"
    >
      <SectionContainer>
        <SectionHeading
          :eyebrow="t('public.home.services.title')"
          :lede="servicesSubtitle(section)"
        >
          <h2>
            <span
              v-for="(part, index) in servicesTitleParts(section)"
              :key="`${part.text}-${index}`"
              :class="{ 'px-hero-highlight': part.highlight }"
            >{{ part.text }}</span>
          </h2>
        </SectionHeading>

        <div class="px-product-grid">
          <article v-for="service in displayServices" :key="service.key" class="px-product-card">
            <div class="px-product-media">
              <img
                v-if="service.image"
                :src="service.image"
                :alt="service.title"
              />
              <div v-else class="px-service-media-fallback" aria-hidden="true">
                <SmartSafetyIcon :name="service.icon" />
              </div>
            </div>
            <div class="px-product-body">
              <h3>{{ service.title }}</h3>
              <p v-if="service.text">{{ service.text }}</p>
              <a href="#contact" class="px-text-link">
                {{ t('public.home.services.learnMore') }}
              </a>
            </div>
          </article>
        </div>
      </SectionContainer>
    </section>

    <section v-else-if="section.type === 'split' && (hasCustomManufacturingContent || displayIndustries.length)" id="custom-manufacturing" class="px-split">
      <div v-if="hasCustomManufacturingContent" class="px-split-custom">
        <div class="px-split-copy">
          <div class="px-split-visual" :class="{ 'has-image': Boolean(manufacturingImage) }">
            <img
              v-if="manufacturingImage"
              :src="manufacturingImage"
              :alt="manufacturingTitle"
              class="px-split-visual-image"
            />
            <EmptyMediaPlaceholder v-else icon="shield" />
          </div>
          <h2 v-if="manufacturingTitle">{{ manufacturingTitle }}</h2>
          <RichTextContent
            v-if="customManufacturing && resolveBilingualField(customManufacturing, 'description', locale)"
            :content="resolveBilingualField(customManufacturing, 'description', locale)"
            tag="div"
          />
          <a v-if="hasManufacturingAction" :href="manufacturingUrl" class="px-btn px-btn-green">{{ manufacturingCta }}</a>
        </div>
      </div>
      <div
        v-if="displayIndustries.length"
        class="px-split-industries"
        :class="{ 'has-image': Boolean(industriesBackground) }"
        :style="industriesBackground ? { backgroundImage: `url('${industriesBackground}')` } : undefined"
      >
        <div class="px-split-industries-inner">
          <h2>{{ resolveSectionTitle(section.industries, 'public.home.industries.title') }}</h2>
          <ul class="px-industry-grid">
            <li v-for="industry in displayIndustries" :key="industry.title">
              <span class="px-industry-icon" aria-hidden="true">
                <img v-if="industry.image" :src="industry.image" :alt="''" />
                <SmartSafetyIcon v-else :name="industry.icon" />
              </span>
              <span>{{ industry.title }}</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <section v-else-if="section.type === 'custom_manufacturing' && hasCustomManufacturingContent" id="custom-manufacturing" class="px-split">
      <div class="px-split-custom">
        <div class="px-split-copy">
          <div class="px-split-visual" :class="{ 'has-image': Boolean(manufacturingImage) }">
            <img
              v-if="manufacturingImage"
              :src="manufacturingImage"
              :alt="manufacturingTitle"
              class="px-split-visual-image"
            />
            <EmptyMediaPlaceholder v-else icon="shield" />
          </div>
          <h2 v-if="manufacturingTitle">{{ manufacturingTitle }}</h2>
          <RichTextContent
            v-if="customManufacturing && resolveBilingualField(customManufacturing, 'description', locale)"
            :content="resolveBilingualField(customManufacturing, 'description', locale)"
            tag="div"
          />
          <a v-if="hasManufacturingAction" :href="manufacturingUrl" class="px-btn px-btn-green">{{ manufacturingCta }}</a>
        </div>
      </div>
    </section>

    <section
      v-else-if="section.type === 'industries' && displayIndustries.length"
      id="industries"
      class="px-split-industries px-split-industries--standalone"
      :class="{ 'has-image': Boolean(industriesBackground) }"
      :style="industriesBackground ? { backgroundImage: `url('${industriesBackground}')` } : undefined"
    >
      <div class="px-split-industries-inner px-container">
        <h2>{{ resolveSectionTitle(section, 'public.home.industries.title') }}</h2>
        <ul class="px-industry-grid">
          <li v-for="industry in displayIndustries" :key="industry.title">
            <span class="px-industry-icon" aria-hidden="true">
              <img v-if="industry.image" :src="industry.image" :alt="''" />
              <SmartSafetyIcon v-else :name="industry.icon" />
            </span>
            <span>{{ industry.title }}</span>
          </li>
        </ul>
      </div>
    </section>

    <section v-else-if="section.type === 'vision_mission' && hasVisionMissionContent" id="vision-mission" class="px-vision-mission">
      <SectionContainer>
        <SectionHeading
          :eyebrow="resolveSectionTitle(section, 'public.home.visionMission.title')"
          :title="resolveSectionHeadline(section, 'public.home.visionMission.headline')"
        />

        <div class="px-vm-grid">
          <article v-if="visionCard.html || visionCard.text" class="px-vm-card">
            <div class="px-vm-icon" aria-hidden="true">
              <SmartSafetyIcon name="vision" />
            </div>
            <p class="px-vm-label">{{ t('public.home.visionMission.visionLabel') }}</p>
            <h3>{{ visionCard.heading }}</h3>
            <RichTextContent
              v-if="visionCard.html"
              :content="visionCard.html"
              tag="div"
              class="px-vm-copy"
            />
            <p v-else class="px-vm-copy">{{ visionCard.text }}</p>
          </article>

          <article v-if="missionCard.html || missionCard.text" class="px-vm-card">
            <div class="px-vm-icon" aria-hidden="true">
              <SmartSafetyIcon name="mission" />
            </div>
            <p class="px-vm-label">{{ t('public.home.visionMission.missionLabel') }}</p>
            <h3>{{ missionCard.heading }}</h3>
            <RichTextContent
              v-if="missionCard.html"
              :content="missionCard.html"
              tag="div"
              class="px-vm-copy"
            />
            <p v-else class="px-vm-copy">{{ missionCard.text }}</p>
          </article>
        </div>
      </SectionContainer>
    </section>

    <section v-else-if="section.type === 'goals' && displayGoals.length" id="goals" class="px-goals">
      <SectionContainer>
        <SectionHeading
          :eyebrow="resolveSectionTitle(section, 'public.home.goals.title')"
          :title="resolveSectionHeadline(section)"
        />
        <ul class="px-goals-grid">
          <li v-for="(goal, index) in displayGoals" :key="`${goal}-${index}`" class="px-goal-card">
            <span class="px-goal-icon" aria-hidden="true">
              <SmartSafetyIcon name="flag" />
            </span>
            <p>{{ goal }}</p>
          </li>
        </ul>
      </SectionContainer>
    </section>

    <section v-else-if="section.type === 'about' && hasAboutContent" id="about" class="px-about">
      <div class="px-container px-about-grid">
        <div class="px-about-copy">
          <p class="px-about-eyebrow">{{ t('public.home.about.title') }}</p>
          <h2>
            <span v-for="(part, index) in aboutTitleParts(section)" :key="`${part.text}-${index}`" :class="{ 'px-hero-highlight': part.highlight }">{{ part.text }}</span>
          </h2>
          <RichTextContent
            v-if="aboutText"
            :content="aboutText"
            tag="div"
            class="px-about-text"
          />

          <div v-if="displayAboutHighlights.length" class="px-about-highlights">
            <article v-for="item in displayAboutHighlights" :key="item.title" class="px-about-highlight">
              <span class="px-about-highlight-icon" aria-hidden="true">
                <SmartSafetyIcon :name="item.icon" />
              </span>
              <div class="px-about-highlight-copy">
                <strong>{{ item.title }}</strong>
                <p v-if="item.text">{{ item.text }}</p>
              </div>
            </article>
          </div>

          <div v-if="displayStats.length" class="px-stats-grid">
            <article v-for="stat in displayStats" :key="stat.label || stat.value" class="px-stat">
              <strong>{{ stat.value }}</strong>
              <span v-if="stat.label">{{ stat.label }}</span>
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
          <EmptyMediaPlaceholder v-else icon="building" tall />
        </div>
      </div>
    </section>

    <section v-else-if="section.type === 'team_members' && teamMembers.length" id="team" class="px-team">
      <SectionContainer>
        <SectionHeading :title="resolveSectionTitle(section, 'public.home.team.title')" />
        <TeamMembersCarousel :members="teamMembers" />
      </SectionContainer>
    </section>

    <section v-else-if="section.type === 'clients_partners' && clientsPartners.length" id="clients-partners" class="px-clients-partners">
      <SectionContainer>
        <SectionHeading :title="resolveSectionTitle(section, 'public.home.clientsPartners.title')" />
        <ClientsPartnersCarousel :items="clientsPartners" />
      </SectionContainer>
    </section>

    <section v-else-if="section.type === 'gallery' && galleryItemsForSection(section).length" id="gallery" class="px-gallery">
      <SectionContainer>
        <SectionHeading :title="resolveSectionTitle(section, 'public.home.gallery.title')" />
        <div class="px-gallery-grid">
          <figure v-for="item in galleryItemsForSection(section)" :key="item.src">
            <img :src="item.src" :alt="item.alt" />
          </figure>
        </div>
      </SectionContainer>
    </section>

    <section v-else-if="section.type === 'contact_cta' && hasContactCtaContent" class="px-contact-cta">
      <div
        class="px-contact-cta-bg"
        :class="{ 'has-image': Boolean(contactCtaBackground) }"
        :style="contactCtaBackground ? { backgroundImage: `url('${contactCtaBackground}')` } : undefined"
      ></div>
      <div class="px-container px-contact-cta-inner">
        <h2 v-if="contactCtaTitle">{{ contactCtaTitle }}</h2>
        <p v-if="contactCtaText">{{ contactCtaText }}</p>
        <a v-if="hasContactCtaAction" :href="contactCtaUrl" class="px-btn px-btn-green">{{ contactCtaLabel }}</a>
      </div>
    </section>

    <section v-else-if="section.type === 'contact_form'" id="contact" class="px-contact">
      <div class="px-container px-contact-grid">
        <div>
          <p class="px-about-eyebrow">{{ t('public.home.contact.eyebrow') }}</p>
          <h2>{{ resolveSectionTitle(section, 'public.home.contact.title') }}</h2>
          <p v-if="contactSubtitle(section)">{{ contactSubtitle(section) }}</p>
          <ul class="px-contact-details">
            <li v-if="companyInfo.phone">
              <span class="px-contact-icon" aria-hidden="true"><SmartSafetyIcon name="phone" /></span>
              <div>
                <span>{{ t('public.home.contact.phone') }}</span>
                <a :href="`tel:${companyInfo.phone}`" dir="ltr">{{ companyInfo.phone }}</a>
              </div>
            </li>
            <li v-if="companyInfo.email">
              <span class="px-contact-icon" aria-hidden="true"><SmartSafetyIcon name="envelope" /></span>
              <div>
                <span>{{ t('public.home.contact.email') }}</span>
                <a :href="`mailto:${companyInfo.email}`">{{ companyInfo.email }}</a>
              </div>
            </li>
            <li v-if="resolveBilingualField(companyInfo, 'address', locale)">
              <span class="px-contact-icon" aria-hidden="true"><SmartSafetyIcon name="location-dot" /></span>
              <div>
                <span>{{ t('public.home.contact.address') }}</span>
                <p>{{ resolveBilingualField(companyInfo, 'address', locale) }}</p>
              </div>
            </li>
            <li v-if="whatsappUrl">
              <span class="px-contact-icon" aria-hidden="true"><SmartSafetyIcon name="whatsapp" /></span>
              <div>
                <span>{{ t('public.home.contact.whatsapp') }}</span>
                <a :href="whatsappUrl" target="_blank" rel="noopener noreferrer" dir="ltr">{{ companyInfo.phone || t('public.home.contact.whatsapp') }}</a>
              </div>
            </li>
          </ul>
        </div>

        <form id="contact-form" class="px-contact-form" novalidate @submit.prevent="submitContactForm">
          <div>
            <label for="contact-name">{{ t('public.home.contact.formName') }} <span aria-hidden="true">*</span></label>
            <input id="contact-name" v-model="contactForm.name" type="text" name="name" required aria-required="true" :placeholder="t('public.home.contact.formNamePlaceholder')" />
            <p v-if="contactForm.errors.name" class="px-form-error">{{ contactForm.errors.name }}</p>
          </div>
          <div class="px-form-row">
            <div>
              <label for="contact-email">{{ t('public.home.contact.formEmail') }} <span aria-hidden="true">*</span></label>
              <input id="contact-email" v-model="contactForm.email" type="email" name="email" required aria-required="true" :placeholder="t('public.home.contact.formEmailPlaceholder')" />
              <p v-if="contactForm.errors.email" class="px-form-error">{{ contactForm.errors.email }}</p>
            </div>
            <div>
              <label for="contact-phone">{{ t('public.home.contact.formPhone') }} <span aria-hidden="true">*</span></label>
              <input id="contact-phone" v-model="contactForm.phone" type="tel" name="phone" required aria-required="true" :placeholder="t('public.home.contact.formPhonePlaceholder')" />
              <p v-if="contactForm.errors.phone" class="px-form-error">{{ contactForm.errors.phone }}</p>
            </div>
          </div>
          <div>
            <label for="contact-subject">{{ t('public.home.contact.formSubject') }} <span aria-hidden="true">*</span></label>
            <input id="contact-subject" v-model="contactForm.subject" type="text" name="subject" required aria-required="true" :placeholder="t('public.home.contact.formSubjectPlaceholder')" />
            <p v-if="contactForm.errors.subject" class="px-form-error">{{ contactForm.errors.subject }}</p>
          </div>
          <div>
            <label for="contact-message">{{ t('public.home.contact.formMessage') }} <span aria-hidden="true">*</span></label>
            <textarea id="contact-message" v-model="contactForm.message" name="message" rows="5" required aria-required="true" :placeholder="t('public.home.contact.formMessagePlaceholder')"></textarea>
            <p v-if="contactForm.errors.message" class="px-form-error">{{ contactForm.errors.message }}</p>
          </div>
          <p v-if="contactForm.errors['g-recaptcha-response']" class="px-form-error">{{ contactForm.errors['g-recaptcha-response'] }}</p>
          <button type="submit" class="px-btn px-btn-green" :disabled="contactForm.processing">
            {{ contactForm.processing ? t('public.home.contact.formSending') : t('public.home.contact.formSend') }}
          </button>
          <p v-if="contactFormSuccess" class="px-form-success">{{ t('public.home.contact.messageSentSuccess') }}</p>
        </form>
      </div>
    </section>
  </template>
  </div>
</template>
