const MAX_LENGTH = 4096
const ALLOWED_HOST = /^(?:maps\.)?(?:www\.)?google\.(?:com|com\.[a-z]{2}|co\.[a-z]{2}|[a-z]{2})$/i

export function sanitizeGoogleMapsEmbedUrl(value) {
  if (typeof value !== 'string') {
    return ''
  }

  const url = value.trim()

  if (!url || url.length > MAX_LENGTH) {
    return ''
  }

  const lower = url.toLowerCase()

  if (
    lower.includes('<')
    || lower.includes('>')
    || lower.includes('iframe')
    || lower.includes('javascript:')
    || lower.includes('data:')
    || /\s/.test(url)
  ) {
    return ''
  }

  let parsed

  try {
    parsed = new URL(url)
  } catch {
    return ''
  }

  if (parsed.protocol !== 'https:' || parsed.username || parsed.password) {
    return ''
  }

  if (!ALLOWED_HOST.test(parsed.hostname)) {
    return ''
  }

  const path = parsed.pathname.toLowerCase()

  if (!path.startsWith('/maps')) {
    return ''
  }

  const hasEmbedPath = path.includes('/embed')
  const output = parsed.searchParams.get('output')
  const hasPb = parsed.searchParams.has('pb')

  if (!hasEmbedPath && output !== 'embed' && !hasPb) {
    return ''
  }

  return url
}
