const DEFAULTS = {
  theme_primary_color: '#7AC142',
  theme_dark_color: '#0B1F3A',
  theme_heading_text_color: '#0B1F3A',
  theme_body_text_color: '#334155',
  theme_muted_text_color: '#64748B',
  theme_nav_text_color: '#0B1F3A',
  theme_nav_hover_text_color: '#1B4F9C',
  theme_hero_text_color: '#FFFFFF',
  theme_on_dark_text_color: '#FFFFFF',
}

function normalizeHex(color) {
  if (!color || typeof color !== 'string') {
    return null
  }

  const normalized = color.trim().toUpperCase()

  return /^#[0-9A-F]{6}$/.test(normalized) ? normalized : null
}

function resolveColor(color, fallback) {
  return normalizeHex(color) || fallback
}

export function resolvePublicTheme(companyInfo) {
  const dark = resolveColor(companyInfo?.theme_dark_color, DEFAULTS.theme_dark_color)

  return {
    theme_primary_color: resolveColor(companyInfo?.theme_primary_color, DEFAULTS.theme_primary_color),
    theme_dark_color: dark,
    theme_heading_text_color: resolveColor(companyInfo?.theme_heading_text_color, DEFAULTS.theme_heading_text_color),
    theme_body_text_color: resolveColor(companyInfo?.theme_body_text_color, DEFAULTS.theme_body_text_color),
    theme_muted_text_color: resolveColor(companyInfo?.theme_muted_text_color, DEFAULTS.theme_muted_text_color),
    theme_nav_text_color: resolveColor(companyInfo?.theme_nav_text_color, DEFAULTS.theme_nav_text_color),
    theme_nav_hover_text_color: resolveColor(companyInfo?.theme_nav_hover_text_color, DEFAULTS.theme_nav_hover_text_color),
    theme_hero_text_color: resolveColor(companyInfo?.theme_hero_text_color, DEFAULTS.theme_hero_text_color),
    theme_on_dark_text_color: resolveColor(companyInfo?.theme_on_dark_text_color, DEFAULTS.theme_on_dark_text_color),
  }
}

export function publicThemeStyle(companyInfo) {
  const theme = resolvePublicTheme(companyInfo)

  return {
    '--sf-yellow': theme.theme_primary_color,
    '--sf-yellow-hover': `color-mix(in srgb, ${theme.theme_primary_color} 85%, #000000)`,
    '--sf-black': theme.theme_dark_color,
    '--sf-gray': theme.theme_muted_text_color,
    '--sf-text-heading': theme.theme_heading_text_color,
    '--sf-text-body': theme.theme_body_text_color,
    '--sf-text-muted': theme.theme_muted_text_color,
    '--sf-text-nav': theme.theme_nav_text_color,
    '--sf-text-nav-hover': theme.theme_nav_hover_text_color,
    '--sf-text-hero': theme.theme_hero_text_color,
    '--sf-text-on-dark': theme.theme_on_dark_text_color,
    '--sf-muted': theme.theme_muted_text_color,
  }
}

export { DEFAULTS as PUBLIC_THEME_DEFAULTS }
