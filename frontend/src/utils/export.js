/**
 * Export data to Excel (CSV format compatible with Excel)
 * @param {Array} data - Array of objects to export
 * @param {Array} columns - Array of { key: string, label: string } defining columns
 * @param {string} filename - Name of the file (without extension)
 */
export function exportToExcel(data, columns, filename = 'export') {
  if (!data || !data.length) {
    alert('No data to export')
    return
  }

  // Create CSV content
  const headers = columns.map(col => `"${col.label}"`).join(',')
  
  const rows = data.map(row => {
    return columns.map(col => {
      let value = row[col.key]
      
      // Handle nested properties (e.g., 'user.name')
      if (col.key.includes('.')) {
        const keys = col.key.split('.')
        value = keys.reduce((obj, key) => obj?.[key], row)
      }
      
      // Format value
      if (value === null || value === undefined) {
        value = ''
      } else if (typeof value === 'number') {
        value = value.toString()
      } else if (typeof value === 'boolean') {
        value = value ? 'Yes' : 'No'
      } else if (value instanceof Date) {
        value = value.toISOString().split('T')[0]
      }
      
      // Escape quotes and wrap in quotes
      value = `"${String(value).replace(/"/g, '""')}"`
      return value
    }).join(',')
  })

  const csvContent = [headers, ...rows].join('\n')
  
  // Add BOM for Excel UTF-8 compatibility
  const BOM = '\uFEFF'
  const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' })
  
  // Create download link
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  link.setAttribute('href', url)
  link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.csv`)
  link.style.visibility = 'hidden'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  
  URL.revokeObjectURL(url)
}

/**
 * Export to Excel with multiple sheets (as separate CSV files in a zip)
 * For simple use cases, use exportToExcel instead
 */
export function exportMultipleSheets(sheets, zipFilename = 'export') {
  // For now, export each sheet as separate download
  // In production, you'd use a library like JSZip
  sheets.forEach((sheet, index) => {
    setTimeout(() => {
      exportToExcel(sheet.data, sheet.columns, sheet.name || `sheet_${index + 1}`)
    }, index * 500) // Stagger downloads
  })
}

/**
 * Format currency for export
 */
export function formatCurrency(amount, currency = 'GH₵') {
  return `${currency} ${(amount || 0).toFixed(2)}`
}

/**
 * Format date for export
 */
export function formatDateForExport(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: '2-digit', 
    year: 'numeric'
  })
}
