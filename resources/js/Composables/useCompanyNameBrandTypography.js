export const COMPANY_NAME_BRAND_DEFAULTS = {
  font_family_ar: "'Cairo', 'Tajawal', Arial, sans-serif",
  font_family_en: "'Poppins', Arial, sans-serif",
  font_size_ar: 0.98,
  font_size_en: 0.74,
  font_weight: 800,
  text_color: '#0B1F3A',
}

const toNumber = (value, fallback) => {
  if (value === null || value === undefined || value === '') {
    return fallback
  }

  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

const resolveFontFamily = (value, fallback) => {
  const family = value?.trim()
  return family || fallback
}

export const resolveCompanyNameBrandTypography = (companyInfo = {}) => ({
  font_family_ar: resolveFontFamily(companyInfo.company_name_font_family_ar, COMPANY_NAME_BRAND_DEFAULTS.font_family_ar),
  font_family_en: resolveFontFamily(companyInfo.company_name_font_family_en, COMPANY_NAME_BRAND_DEFAULTS.font_family_en),
  font_size_ar: toNumber(companyInfo.company_name_font_size_ar, COMPANY_NAME_BRAND_DEFAULTS.font_size_ar),
  font_size_en: toNumber(companyInfo.company_name_font_size_en, COMPANY_NAME_BRAND_DEFAULTS.font_size_en),
  font_weight: toNumber(companyInfo.company_name_font_weight, COMPANY_NAME_BRAND_DEFAULTS.font_weight),
  text_color: companyInfo.company_name_text_color?.trim() || COMPANY_NAME_BRAND_DEFAULTS.text_color,
})

export const companyNameBrandCssVars = (companyInfo = {}) => {
  const typography = resolveCompanyNameBrandTypography(companyInfo)

  return {
    '--px-brand-font-family-ar': typography.font_family_ar,
    '--px-brand-font-family-en': typography.font_family_en,
    '--px-brand-font-size-ar': `${typography.font_size_ar}rem`,
    '--px-brand-font-size-en': `${typography.font_size_en}rem`,
    '--px-brand-font-weight': String(typography.font_weight),
    '--px-brand-color': typography.text_color,
  }
}

export const companyNameBrandArFontStyle = (companyInfo = {}) => ({
  fontFamily: resolveCompanyNameBrandTypography(companyInfo).font_family_ar,
})

export const companyNameBrandEnFontStyle = (companyInfo = {}) => ({
  fontFamily: resolveCompanyNameBrandTypography(companyInfo).font_family_en,
})
