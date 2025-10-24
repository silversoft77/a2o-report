import type { Options, SeriesOptionsType } from 'highcharts'

interface ChartData {
    categories?: string[]
    series?: Array<{
        name: string
        data: number[]
        type?: string
        color?: string
        fillColor?: string
    }>
}

export const createJobBookingsChartOptions = (
    formattedCategories: string[],
    coloredSeries: ChartData['series'] = []
): Options => ({
    chart: {
        type: 'areaspline',
        height: 420,
        backgroundColor: 'transparent',
        spacingTop: 60
    },
    title: { text: '', style: { color: '#F3F4F6' } },
    legend: {
        layout: 'horizontal',
        align: 'center',
        verticalAlign: 'top',
        itemStyle: { color: '#F3F4F6' }
    },
    xAxis: {
        categories: formattedCategories,
        labels: { style: { color: '#D1D5DB' } },
        gridLineColor: '#374151'
    },
    yAxis: {
        title: { text: '', style: { color: '#D1D5DB' } },
        labels: { style: { color: '#D1D5DB' } },
        gridLineColor: '#374151'
    },
    tooltip: { backgroundColor: '#111827', style: { color: '#F3F4F6' } },
    plotOptions: {
        areaspline: {
            marker: { enabled: true },
            lineWidth: 3,
            fillOpacity: 0.1,
            states: { hover: { lineWidthPlus: 2 } }
        }
    },
    series: coloredSeries as unknown as SeriesOptionsType[],
    credits: { enabled: false }
})