import { resolveBilingualField } from './useBilingualContent.js'
import { plainTextFromHtml } from './useRichText.js'

export function resolveHomepageField(companyInfo, field, locale, fallback = '') {
  const value = resolveBilingualField(companyInfo || {}, field, locale)

  if (value) {
    return value
  }

  return fallback
}

export function resolveHomepagePlainField(companyInfo, field, locale, fallback = '') {
  const value = resolveHomepageField(companyInfo, field, locale, fallback)

  return plainTextFromHtml(value)
}

export function resolveHomepageScalar(companyInfo, field, fallback = '') {
  const value = companyInfo?.[field]

  if (value === null || value === undefined || value === '') {
    return fallback
  }

  return value
}

export function formatHomepageTemplate(template, replacements) {
  return Object.entries(replacements).reduce(
    (result, [key, value]) => result.replaceAll(`{${key}}`, String(value)),
    template,
  )
}
