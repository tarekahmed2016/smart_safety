export function formatVisitorChartDate(date) {
    if (!date) {
        return ''
    }

    const parts = String(date).split('-')
    if (parts.length !== 3) {
        return String(date)
    }

    const day = Number(parts[2])
    const month = Number(parts[1])

    if (!Number.isFinite(day) || !Number.isFinite(month) || day < 1 || month < 1) {
        return String(date)
    }

    return `${day}/${month}`
}

export function visitorChartDaysForPlot(days) {
    const items = Array.isArray(days) ? days.slice() : []

    return items
        .filter((day) => day?.date)
        .sort((left, right) => String(left.date).localeCompare(String(right.date)))
        .slice(-7)
}
