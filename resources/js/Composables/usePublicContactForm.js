export const PUBLIC_CONTACT_OPEN_EVENT = 'public:open-contact-form'

const CONTACT_HASHES = new Set(['contact', 'contact-form'])
const CONTACT_EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const CONTACT_PHONE_PATTERN = /^\+?[0-9][0-9\s\-()]{6,48}$/

export const CONTACT_FORM_ERROR_MESSAGES = {
  name: 'يرجى إدخال الاسم الكامل. / Please enter your full name.',
  email: 'يرجى إدخال البريد الإلكتروني. / Please enter your email address.',
  emailInvalid: 'يرجى إدخال بريد إلكتروني صالح. / Please enter a valid email address.',
  phone: 'يرجى إدخال رقم الهاتف. / Please enter your phone number.',
  phoneInvalid: 'يرجى إدخال رقم هاتف صالح. / Please enter a valid phone number.',
  subject: 'يرجى إدخال الموضوع. / Please enter the subject.',
  message: 'يرجى إدخال الرسالة. / Please enter your message.',
  recaptcha: 'يرجى إكمال التحقق من reCAPTCHA. / Please complete the reCAPTCHA verification.',
}

function trimmedValue(value) {
  return typeof value === 'string' ? value.trim() : ''
}

export function isValidContactEmail(value) {
  return CONTACT_EMAIL_PATTERN.test(trimmedValue(value))
}

export function isValidContactPhone(value) {
  return CONTACT_PHONE_PATTERN.test(trimmedValue(value))
}

export function collectPublicContactFormErrors(form, { recaptchaEnabled = false } = {}) {
  const errors = {}
  const name = trimmedValue(form?.name)
  const email = trimmedValue(form?.email)
  const phone = trimmedValue(form?.phone)
  const subject = trimmedValue(form?.subject)
  const message = trimmedValue(form?.message)
  const recaptcha = trimmedValue(form?.['g-recaptcha-response'])

  if (!name) {
    errors.name = CONTACT_FORM_ERROR_MESSAGES.name
  }

  if (!email) {
    errors.email = CONTACT_FORM_ERROR_MESSAGES.email
  } else if (!isValidContactEmail(email)) {
    errors.email = CONTACT_FORM_ERROR_MESSAGES.emailInvalid
  }

  if (!phone) {
    errors.phone = CONTACT_FORM_ERROR_MESSAGES.phone
  } else if (!isValidContactPhone(phone)) {
    errors.phone = CONTACT_FORM_ERROR_MESSAGES.phoneInvalid
  }

  if (!subject) {
    errors.subject = CONTACT_FORM_ERROR_MESSAGES.subject
  }

  if (!message) {
    errors.message = CONTACT_FORM_ERROR_MESSAGES.message
  }

  if (recaptchaEnabled && !recaptcha) {
    errors['g-recaptcha-response'] = CONTACT_FORM_ERROR_MESSAGES.recaptcha
  }

  return errors
}


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
