export const PUBLIC_CONTACT_OPEN_EVENT = 'public:open-contact-form'

const CONTACT_HASHES = new Set(['contact', 'contact-form'])

export function isPublicContactHref(href, location = typeof window === 'undefined' ? null : window.location) {
  if (!href || typeof href !== 'string') {
    return false
  }

  const trimmed = href.trim()
  if (!trimmed || /^(mailto:|tel:|javascript:|sms:)/i.test(trimmed)) {
    return false
  }

  try {
    const base = location?.href || 'http://local.invalid/'
    const url = new URL(trimmed, base)
    const hash = url.hash.replace(/^#/, '')

    if (!CONTACT_HASHES.has(hash)) {
      return false
    }

    if (location && url.origin !== new URL(base).origin) {
      return false
    }

    const path = url.pathname.replace(/\/+$/, '') || '/'

    return path === '/'
  } catch {
    return false
  }
}

export function isOnPublicHome(location = typeof window === 'undefined' ? null : window.location) {
  if (!location) {
    return false
  }

  return (location.pathname.replace(/\/+$/, '') || '/') === '/'
}

export function scrollToPublicContact() {
  const section = document.getElementById('contact') || document.getElementById('contact-form')

  section?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

export function openPublicContactForm({ reset = true, focusId = 'contact-name' } = {}) {
  if (typeof window === 'undefined') {
    return
  }

  window.dispatchEvent(new CustomEvent(PUBLIC_CONTACT_OPEN_EVENT, {
    detail: { reset, focusId },
  }))
}

export function handlePublicContactLinkClick(event) {
  if (event.defaultPrevented || event.button) {
    return
  }

  if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
    return
  }

  const anchor = event.target?.closest?.('a[href]')
  if (!anchor || anchor.target === '_blank') {
    return
  }

  const href = anchor.getAttribute('href')
  if (!isPublicContactHref(href)) {
    return
  }

  if (!isOnPublicHome()) {
    return
  }

  event.preventDefault()
  openPublicContactForm({ reset: true, focusId: 'contact-name' })
}
