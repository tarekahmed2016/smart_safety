import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from './useBilingualContent.js'

export function usePublicNavLinks() {
  const { t, locale } = useI18n()
  const page = usePage()

  const menuPages = computed(() => page.props.menuPages || [])
  const isHomePage = computed(() => page.url === '/' || page.url === '')

  const homePrefix = computed(() => (isHomePage.value ? '' : route('home')))

  const navLinks = computed(() => {
    const items = [
      {
        key: 'home',
        order: 10,
        href: `${homePrefix.value}#home`,
        label: t('public.home.nav.home'),
      },
      {
        key: 'about',
        order: 20,
        href: `${homePrefix.value}#about`,
        label: t('public.home.nav.about'),
      },
      {
        key: 'products',
        order: 30,
        href: isHomePage.value ? '#products' : route('public.products.index'),
        label: t('public.home.nav.products'),
      },
      {
        key: 'custom-manufacturing',
        order: 40,
        href: `${homePrefix.value}#custom-manufacturing`,
        label: t('public.home.nav.customManufacturing'),
      },
      {
        key: 'gallery',
        order: 50,
        href: `${homePrefix.value}#gallery`,
        label: t('public.home.nav.gallery'),
      },
    ]

    menuPages.value.forEach((customPage) => {
      items.push({
        key: `page-${customPage.slug}`,
        order: customPage.menu_order,
        href: route('public.page.show', { slug: customPage.slug }),
        label: resolveBilingualField(customPage, 'menu_title', locale.value),
      })
    })

    items.push({
      key: 'contact',
      order: 100,
      href: `${homePrefix.value}#contact`,
      label: t('public.home.nav.contact'),
    })

    return items.sort((a, b) => a.order - b.order)
  })

  return {
    navLinks,
    isHomePage,
    homePrefix,
  }
}
