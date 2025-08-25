export const formatDate = (dateString) => {
  const date = new Date(dateString);
  console.log(date);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(date);
}

export const isEditPage = (url)  => {
  return url?.includes('edit')
}

export const formatDateShort = (dateString) => {
  const d = new Date(dateString)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export const checkDateStatus = (dateInput) => {
    const now = new Date();
    const givenDate = new Date(dateInput);
    const diffMs = now - givenDate; // difference in milliseconds

    const daysDiff = diffMs / (1000 * 60 * 60 * 24);

    return daysDiff > 1;
}

export const formatAmount = (amount) => {
  return `₱${Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}