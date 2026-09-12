import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from './useBilingualContent.js'

const fallbackNavLinks = (t, locale, menuPages, isHomePage) => {
  const homePrefix = isHomePage ? '' : route('home')
  const items = [
    { key: 'home', order: 10, href: `${homePrefix}#home`, label: t('public.home.nav.home'), open_in_new_tab: false },
    { key: 'about', order: 20, href: `${homePrefix}#about`, label: t('public.home.nav.about'), open_in_new_tab: false },
    { key: 'why-us', order: 25, href: `${homePrefix}#why-us`, label: t('public.home.nav.whyUs'), open_in_new_tab: false },
    { key: 'services', order: 30, href: `${homePrefix}#services`, label: t('public.home.nav.services'), open_in_new_tab: false },
    { key: 'products', order: 40, href: isHomePage ? '#products' : route('public.products.index'), label: t('public.home.nav.products'), open_in_new_tab: false },
    { key: 'vision', order: 50, href: `${homePrefix}#vision-mission`, label: t('public.home.nav.vision'), open_in_new_tab: false },
    { key: 'goals', order: 60, href: `${homePrefix}#goals`, label: t('public.home.nav.goals'), open_in_new_tab: false },
    { key: 'clients', order: 70, href: `${homePrefix}#clients-partners`, label: t('public.home.nav.clients'), open_in_new_tab: false },
    { key: 'gallery', order: 80, href: `${homePrefix}#gallery`, label: t('public.home.nav.gallery'), open_in_new_tab: false },
    { key: 'custom-manufacturing', order: 90, href: `${homePrefix}#custom-manufacturing`, label: t('public.home.nav.customManufacturing'), open_in_new_tab: false },
  ]

  menuPages.forEach((customPage) => {
    items.push({
      key: `page-${customPage.slug}`,
      order: customPage.menu_order,
      href: route('public.page.show', { slug: customPage.slug }),
      label: resolveBilingualField(customPage, 'menu_title', locale.value),
      open_in_new_tab: Boolean(customPage.open_in_new_tab),
    })
  })

  items.push({
    key: 'contact',
    order: 100,
    href: `${homePrefix}#contact-form`,
    label: t('public.home.nav.contact'),
    open_in_new_tab: false,
  })

  return items.sort((a, b) => a.order - b.order)
}

export function usePublicNavLinks() {
  const { t, locale } = useI18n()
  const page = usePage()

  const menuPages = computed(() => page.props.menuPages || [])
  const isHomePage = computed(() => page.url === '/' || page.url === '')

  const navLinks = computed(() => {
    const managedLinks = page.props.navigationLinks || []

    if (!managedLinks.length) {
      return fallbackNavLinks(t, locale, menuPages.value, isHomePage.value)
    }

    return managedLinks
      .map((link) => ({
        key: link.key,
        order: link.order,
        href: link.href,
        label: locale.value === 'ar'
          ? (link.label_ar || link.label_en || '')
          : (link.label_en || link.label_ar || ''),
        open_in_new_tab: Boolean(link.open_in_new_tab),
      }))
      .sort((left, right) => left.order - right.order)
  })

  const homePrefix = computed(() => (isHomePage.value ? '' : route('home')))

  return {
    navLinks,
    isHomePage,
    homePrefix,
  }
}
