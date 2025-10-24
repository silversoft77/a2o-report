import { formatISODate } from './dateUtils'
import { MESSAGES } from '@/constants/text'

interface DateRange {
    fromDate: Date | null | undefined
    toDate: Date | null | undefined
}

export const createDateRangeParams = (
    { fromDate, toDate }: DateRange,
    marketIds?: string[] | null
): Record<string, any> => {
    const params: Record<string, any> = {
        fromDate: formatISODate(fromDate),
        toDate: formatISODate(toDate)
    }

    if (Array.isArray(marketIds) && marketIds.length > 0) {
        params.markets = marketIds.join(',')
    }

    return params
}

export const downloadJobBookingsCSV = async (
    dateRange: DateRange,
    marketIds?: string[] | null
): Promise<void> => {
    const params = createDateRangeParams(dateRange, marketIds)
    const qs = new URLSearchParams({ ...params, export: 'csv' }).toString()
    const url = `/api/reports/job-bookings?${qs}`

    try {
        const axiosModule = await import('axios')
        const axios = axiosModule.default
        const resp = await axios.get(url, { responseType: 'blob' })
        const blob = resp.data as Blob

        let filename = 'job_bookings.csv'
        const cd = resp.headers && (resp.headers['content-disposition'] || resp.headers['Content-Disposition'])
        if (cd) {
            const match = /filename\*=UTF-8''(.+)$/.exec(cd) || /filename="?([^";]+)"?/.exec(cd)
            if (match && match[1]) {
                filename = decodeURIComponent(match[1])
            }
        }

        const blobUrl = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = blobUrl
        a.download = filename
        document.body.appendChild(a)
        a.click()
        a.remove()
        URL.revokeObjectURL(blobUrl)
    } catch (err) {
        console.error(MESSAGES.CSV_DOWNLOAD_FAILED, err)
        const urlFallback = `/api/reports/job-bookings?${new URLSearchParams({ ...params, export: 'csv' })}`
        window.open(urlFallback, '_blank')
    }
}

export const downloadCSVReport = (
    endpoint: string,
    params: Record<string, any>
): void => {
    const qs = new URLSearchParams({ ...params, export: 'csv' }).toString()
    window.open(`${endpoint}?${qs}`, '_blank')
}

export const downloadReportCSVAsync = async (
    endpoint: string,
    params: Record<string, any>
): Promise<void> => {
    const qs = new URLSearchParams({ ...params, export: 'csv' }).toString()
    const url = `${endpoint}?${qs}`
    try {
        const axiosModule = await import('axios')
        const axios = axiosModule.default
        const resp = await axios.get(url, { responseType: 'blob' })
        const blob = resp.data as Blob
        let filename = 'export.csv'
        const cd = resp.headers && (resp.headers['content-disposition'] || resp.headers['Content-Disposition'])
        if (cd) {
            const match = /filename\*=UTF-8''(.+)$/.exec(cd) || /filename="?([^";]+)"?/.exec(cd)
            if (match && match[1]) filename = decodeURIComponent(match[1])
        }
        const blobUrl = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = blobUrl
        a.download = filename
        document.body.appendChild(a)
        a.click()
        a.remove()
        URL.revokeObjectURL(blobUrl)
    } catch (err) {
        console.error(MESSAGES.CSV_DOWNLOAD_FAILED_GENERIC, err)
        window.open(url, '_blank')
    }
}