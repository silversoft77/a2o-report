export const hexToRgba = (hex: string, alpha: number): string => {
    const h = hex.replace('#', '')
    const bigint = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16)
    const r = (bigint >> 16) & 255
    const g = (bigint >> 8) & 255
    const b = bigint & 255
    return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

export const BLUE_PALETTE = ['#60A5FA', '#3B82F6', '#2563EB', '#1D4ED8', '#1E40AF'] as const