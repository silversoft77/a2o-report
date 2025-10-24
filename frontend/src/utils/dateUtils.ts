export const formatShortDate = (dateString: string): string => {
    try {
        const dt = new Date(dateString)
        return dt.toLocaleString(undefined, { month: 'short', day: 'numeric' })
    } catch (e) {
        return dateString
    }
}

export const formatISODate = (date: Date | null | undefined): string | undefined => {
    return date?.toISOString?.()?.split('T')[0]
}

export const formatDateCategories = (categories: string[] = []): string[] => {
    return categories.map(formatShortDate)
}