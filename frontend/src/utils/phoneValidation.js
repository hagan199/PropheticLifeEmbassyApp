/**
 * Phone number validation utilities
 * Supports Ghana numbers and international numbers
 */

// Valid Ghana mobile prefixes
const GHANA_PREFIXES = [
  '20',
  '23',
  '24',
  '25',
  '26',
  '27',
  '28',
  '29',
  '50',
  '54',
  '55',
  '56',
  '57',
  '59',
];

/**
 * Validate a phone number
 * @param {string} phone - The phone number to validate
 * @returns {string|null} - Error message if invalid, null if valid
 */
export function validatePhone(phone) {
  if (!phone) {
    return 'Phone number is required';
  }

  const digits = phone.replace(/\D/g, '');

  // Must have at least 9 digits
  if (digits.length < 9) {
    return 'Phone number must be at least 9 digits';
  }

  // Check for excessive repeating digits (more than 5 same digits in a row)
  if (/(.)\1{5,}/.test(digits)) {
    return 'Invalid phone number - too many repeating digits';
  }

  // Check for repeating patterns
  const lastEight = digits.slice(-8);

  // Check for 2-digit repeating pattern (e.g., 04040404)
  if (/^(\d{2})\1{3}$/.test(lastEight)) {
    return 'Invalid phone number - repeating pattern detected';
  }

  // Check for 4-digit repeating pattern (e.g., 12341234)
  if (/^(\d{4})\1$/.test(lastEight)) {
    return 'Invalid phone number - repeating pattern detected';
  }

  // Check for sequential patterns (ascending/descending)
  const sequential = '01234567890123456789';
  const reverseSequential = '98765432109876543210';
  if (sequential.includes(lastEight) || reverseSequential.includes(lastEight)) {
    return 'Invalid phone number - sequential pattern detected';
  }

  // Extract local number for Ghana validation
  let localNumber = digits;
  let isInternational = phone.startsWith('+') || digits.startsWith('00');

  if (digits.startsWith('233')) {
    localNumber = digits.slice(3);
  } else if (digits.startsWith('0')) {
    localNumber = digits.slice(1);
  }

  // If it looks like a Ghana number (9 digits after removing country code/0)
  if (localNumber.length === 9 && !isInternational) {
    const prefix = localNumber.slice(0, 2);
    if (!GHANA_PREFIXES.includes(prefix)) {
      return 'Invalid Ghana mobile number prefix. Use + for international numbers.';
    }
  }

  return null; // Valid
}

/**
 * Normalize phone number to international format
 * @param {string} input - Raw phone input
 * @returns {string} - Normalized phone number with country code
 */
export function normalizePhone(input) {
  if (!input) return '';

  // Remove all non-digit characters except +
  const cleaned = input.replace(/[^\d+]/g, '');

  // If already starts with +, it's international - keep as is
  if (cleaned.startsWith('+')) {
    return cleaned;
  }

  const digits = cleaned.replace(/\D/g, '');
  if (!digits) return '';

  // If starts with 00, convert to + (international format)
  if (digits.startsWith('00')) {
    return `+${digits.slice(2)}`;
  }

  // If starts with 233 (Ghana), add +
  if (digits.startsWith('233')) {
    return `+${digits}`;
  }

  // If starts with 0, it's a local Ghana number - convert to +233
  if (digits.startsWith('0')) {
    return `+233${digits.slice(1)}`;
  }

  // Otherwise assume it's Ghana without leading 0
  return `+233${digits}`;
}

/**
 * Strip country code from phone number for display
 * @param {string} phone - Phone number with country code
 * @returns {string} - Local number without country code
 */
export function stripCountryCode(phone) {
  if (!phone) return '';
  // Remove +233 or 233
  return phone.replace(/^\+?233/, '0');
}

/**
 * Format phone number for display
 * @param {string} phone - Raw phone number
 * @returns {string} - Formatted phone number
 */
export function formatPhone(phone) {
  if (!phone) return '';
  const stripped = stripCountryCode(phone);
  // Format as 024 123 4567
  if (stripped.length === 10 && stripped.startsWith('0')) {
    return `${stripped.slice(0, 3)} ${stripped.slice(3, 6)} ${stripped.slice(6)}`;
  }
  return phone;
}

export default {
  validatePhone,
  normalizePhone,
  stripCountryCode,
  formatPhone,
  GHANA_PREFIXES,
};
