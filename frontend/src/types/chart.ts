import type { Chart } from 'highcharts'

export interface JobBookingsSeries {
    name: string
    data: number[]
    type?: 'areaspline'
    color?: string
    fillColor?: string
}

export interface JobBookingsData {
    categories: string[]
    series: JobBookingsSeries[]
}

export interface ChartData {
    categories: string[]
    series: Array<{
        name: string
        data: number[]
        type?: string
        color?: string
        fillColor?: string
    }>
}

export type HighchartsInstance = typeof Chart