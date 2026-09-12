/**
 * Resolve a bilingual database field for the active locale with cross-language fallback.
 *
 * @param {Record<string, unknown>|null|undefined} item
 * @param {string} field Base field name without locale suffix (e.g. "name", "description")
 * @param {string} locale Active locale code ("ar" or "en")
 * @returns {string}
 */
export function resolveBilingualField(item, field, locale) {
  if (!item) {
    return ''
  }

  const primaryKey = `${field}_${locale}`
  const fallbackLocale = locale === 'ar' ? 'en' : 'ar'
  const fallbackKey = `${field}_${fallbackLocale}`

  return item[primaryKey] || item[fallbackKey] || ''
}

/**
 * Resolve the admin table/sort field name for the active dashboard locale.
 *
 * @param {string} field Base field name without locale suffix
 * @param {string} locale Active locale code ("ar" or "en")
 * @returns {string}
 */
export function bilingualFieldKey(field, locale) {
  return `${field}_${locale}`
}

/**
 * Keep Arabic titles readable when they also contain Latin product codes.
 *
 * @param {string} text
 * @param {string} locale
 * @returns {string}
 */
export function sanitizeLocalizedTitle(text, locale) {
  const value = String(text || '').trim()

  if (!value) {
    return ''
  }

  if (locale !== 'ar') {
    return value
  }

  return value.replace(/\s*\([^)]*[A-Za-z][^)]*\)/g, '').replace(/\s{2,}/g, ' ').trim()
}

/**
 * @param {string} text
 * @returns {{text: string, isolate: boolean}[]}
 */
export function mixedScriptParts(text) {
  const value = String(text || '')

  if (!value) {
    return []
  }

  const parts = []
  const latinRun = /[A-Za-z0-9][A-Za-z0-9+./&_-]*/g
  let lastIndex = 0
  let match = latinRun.exec(value)

  while (match) {
    if (match.index > lastIndex) {
      parts.push({ text: value.slice(lastIndex, match.index), isolate: false })
    }

    parts.push({ text: match[0], isolate: true })
    lastIndex = match.index + match[0].length
    match = latinRun.exec(value)
  }

  if (lastIndex < value.length) {
    parts.push({ text: value.slice(lastIndex), isolate: false })
  }

  return parts
}
