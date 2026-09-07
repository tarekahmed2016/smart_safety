<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm, usePage } from '@inertiajs/vue3'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { plainTextFromHtml } from '../../Composables/useRichText.js'
import { usePublicNavLinks } from '../../Composables/usePublicNavLinks.js'
import SocialLinks from './SocialLinks.vue'

const { t, locale } = useI18n()
const page = usePage()
const { navLinks } = usePublicNavLinks()

const companyInfo = computed(() => page.props.companyInfo || {})
const companyName = computed(() =>
    resolveBilingualField(companyInfo.value, 'name', locale.value) || t('public.home.defaultCompanyName')
)
const aboutText = computed(() => plainTextFromHtml(resolveBilingualField(companyInfo.value, 'about', locale.value)))
const heroDescription = computed(() => plainTextFromHtml(resolveBilingualField(companyInfo.value, 'hero_description', locale.value)))
const footerDescription = computed(() => aboutText.value || heroDescription.value || t('public.home.footer.tagline'))
const logo = computed(() => companyInfo.value.logo || companyInfo.value.attachment?.asset_path || '/images/plastex/logo.svg')
const addressText = computed(() => resolveBilingualField(companyInfo.value, 'address', locale.value))
const year = new Date().getFullYear()

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
    <footer class="px-footer">
        <div class="px-container px-footer-grid">
            <div class="px-footer-brand">
                <img
                    v-if="logo"
                    :src="logo"
                    :alt="companyName"
                    class="px-footer-logo"
                />
                <h3 v-else class="px-footer-name">{{ companyName }}</h3>
                <p>{{ footerDescription }}</p>
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

            <div>
                <h4>{{ t('public.home.footer.contact') }}</h4>
                <div class="px-footer-contact">
                    <a v-if="companyInfo.phone" :href="`tel:${companyInfo.phone}`">
                        <span dir="ltr">{{ companyInfo.phone }}</span>
                    </a>
                    <a v-if="companyInfo.email" :href="`mailto:${companyInfo.email}`">
                        {{ companyInfo.email }}
                    </a>
                    <p v-if="addressText">{{ addressText }}</p>
                    <p v-if="!companyInfo.phone && !companyInfo.email && !addressText">
                        {{ t('public.home.contact.notAvailable') }}
                    </p>
                </div>
            </div>

            <div>
                <h4>{{ t('public.home.newsletter.title') }}</h4>
                <p class="px-footer-newsletter-copy">{{ t('public.home.newsletter.subtitle') }}</p>
                <form class="px-footer-newsletter" @submit.prevent="submitNewsletter">
                    <label class="sr-only" for="footer-newsletter-email">
                        {{ t('public.home.newsletter.emailPlaceholder') }}
                    </label>
                    <input
                        id="footer-newsletter-email"
                        v-model="newsletterForm.email"
                        type="email"
                        required
                        maxlength="255"
                        :placeholder="t('public.home.newsletter.emailPlaceholder')"
                    />
                    <button type="submit" class="px-btn px-btn-green" :disabled="newsletterForm.processing">
                        {{ newsletterForm.processing ? t('public.home.newsletter.submitting') : t('public.home.newsletter.submit') }}
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
            <div class="px-container">
                {{ t('public.home.footer.copyright', { year, company: companyName }) }}
            </div>
        </div>
    </footer>
</template>
