<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm, usePage } from '@inertiajs/vue3'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { plainTextFromHtml } from '../../Composables/useRichText.js'
import { formatHomepageTemplate, resolveHomepageField, resolveHomepagePlainField } from '../../Composables/useHomepageContent.js'
import { usePublicNavLinks } from '../../Composables/usePublicNavLinks.js'
import { sanitizeGoogleMapsEmbedUrl } from '../../Utils/googleMapsEmbedUrl.js'
import SocialLinks from './SocialLinks.vue'

const { t, locale } = useI18n()
const page = usePage()
const { navLinks } = usePublicNavLinks()

const companyInfo = computed(() => page.props.companyInfo || {})
const companyName = computed(() =>
    resolveBilingualField(companyInfo.value, 'name', locale.value) || t('public.home.defaultCompanyName')
)
const aboutText = computed(() => plainTextFromHtml(resolveBilingualField(companyInfo.value, 'about', locale.value)))
const footerDescription = computed(() =>
  resolveHomepagePlainField(companyInfo.value, 'footer_description', locale.value)
    || aboutText.value
    || ''
)
const googleMapsEmbedUrl = computed(() => sanitizeGoogleMapsEmbedUrl(companyInfo.value.google_maps_embed_url))
const newsletterTitle = computed(() =>
  resolveHomepageField(companyInfo.value, 'footer_newsletter_title', locale.value, t('public.home.newsletter.title'))
)
const newsletterSubtitle = computed(() =>
  resolveHomepageField(companyInfo.value, 'footer_newsletter_description', locale.value, t('public.home.newsletter.subtitle'))
)
const newsletterPlaceholder = computed(() =>
  resolveHomepageField(companyInfo.value, 'footer_newsletter_placeholder', locale.value, t('public.home.newsletter.emailPlaceholder'))
)
const newsletterButton = computed(() =>
  resolveHomepageField(companyInfo.value, 'footer_newsletter_button', locale.value, t('public.home.newsletter.submit'))
)
const copyrightText = computed(() => formatHomepageTemplate(
  resolveHomepageField(companyInfo.value, 'footer_copyright', locale.value, t('public.home.footer.copyright')),
  { year, company: companyName.value },
))
const logo = computed(() => companyInfo.value.logo || companyInfo.value.attachment?.asset_path || '')
const addressText = computed(() => resolveBilingualField(companyInfo.value, 'address', locale.value))
const hasFooterContact = computed(() => Boolean(
  companyInfo.value.phone || companyInfo.value.email || addressText.value
))
const year = new Date().getFullYear()
const todayVisitors = computed(() => Number(page.props.todayVisitors ?? 0))
const showFooterNewsletter = false

const newsletterForm = useForm({
    email: '',
})
const newsletterSuccess = ref(false)
const newsletterAlreadySubscribed = ref(false)

watch(() => page.props.flash, (flash) => {
    if (flash?.success === 'newsletter_subscribed') {
        newsletterSuccess.value = true
        newsletterAlreadySubscribed.value = false
    }

    if (flash?.info === 'newsletter_already_subscribed') {
        newsletterAlreadySubscribed.value = true
        newsletterSuccess.value = false
    }
}, { immediate: true, deep: true })

const submitNewsletter = () => {
    newsletterSuccess.value = false
    newsletterAlreadySubscribed.value = false

    newsletterForm.post(route('newsletter.store'), {
        preserveScroll: true,
        onSuccess: () => {
            newsletterForm.reset()
        },
    })
}
</script>

<template>
    <footer class="px-footer ss-footer">
        <div class="px-container px-footer-grid">
            <div class="px-footer-brand">
                <img
                    v-if="logo"
                    :src="logo"
                    :alt="companyName"
                    class="px-footer-logo"
                />
                <h3 class="px-footer-name">{{ companyName }}</h3>
                <div v-if="googleMapsEmbedUrl" class="px-footer-map">
                    <iframe
                        :src="googleMapsEmbedUrl"
                        width="100%"
                        height="220"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        :title="t('public.home.footer.mapTitle')"
                    />
                </div>
                <p v-else-if="footerDescription">{{ footerDescription }}</p>
                <SocialLinks :company-info="companyInfo" variant="footer" />
            </div>

            <div>
                <h4>{{ t('public.home.footer.quickLinks') }}</h4>
                <nav class="px-footer-links" :aria-label="t('public.home.footer.quickLinks')">
                    <a v-for="link in navLinks" :key="link.key" :href="link.href">
                        {{ link.label }}
                    </a>
                </nav>
            </div>

            <div v-if="hasFooterContact">
                <h4>{{ t('public.home.footer.contact') }}</h4>
                <div class="px-footer-contact">
                    <a v-if="companyInfo.phone" :href="`tel:${companyInfo.phone}`">
                        <span dir="ltr">{{ companyInfo.phone }}</span>
                    </a>
                    <a v-if="companyInfo.email" :href="`mailto:${companyInfo.email}`">
                        {{ companyInfo.email }}
                    </a>
                    <p v-if="addressText">{{ addressText }}</p>
                </div>
            </div>

            <div v-if="showFooterNewsletter">
                <h4>{{ newsletterTitle }}</h4>
                <p class="px-footer-newsletter-copy">{{ newsletterSubtitle }}</p>
                <form class="px-footer-newsletter" @submit.prevent="submitNewsletter">
                    <label class="sr-only" for="footer-newsletter-email">
                        {{ newsletterPlaceholder }}
                    </label>
                    <input
                        id="footer-newsletter-email"
                        v-model="newsletterForm.email"
                        type="email"
                        required
                        maxlength="255"
                        :placeholder="newsletterPlaceholder"
                    />
                    <button type="submit" class="px-btn px-btn-green" :disabled="newsletterForm.processing">
                        {{ newsletterForm.processing ? t('public.home.newsletter.submitting') : newsletterButton }}
                    </button>
                </form>
                <p v-if="newsletterForm.errors.email" class="px-form-error">
                    {{ newsletterForm.errors.email }}
                </p>
                <p v-if="newsletterSuccess" class="px-form-success">
                    {{ t('public.home.newsletter.success') }}
                </p>
                <p v-if="newsletterAlreadySubscribed" class="px-form-info">
                    {{ t('public.home.newsletter.alreadySubscribed') }}
                </p>
            </div>
        </div>

        <div class="px-footer-bottom">
            <div class="px-container px-footer-bottom-inner">
                <span>{{ copyrightText }}</span>
                <span>{{ t('public.home.footer.todayVisitors', { count: todayVisitors }) }}</span>
            </div>
        </div>
    </footer>
</template>
