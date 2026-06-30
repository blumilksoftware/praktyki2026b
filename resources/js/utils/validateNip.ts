export function validateNip(value: string): boolean {
  const nip = value.replace(/\D/g, '')

  if (!/^\d{10}$/.test(nip) || nip === '0000000000') {
    return false
  }

  const weights = [6, 5, 7, 2, 3, 4, 5, 6, 7]
  let sum = 0

  for (let i = 0; i < 9; i++) {
    sum += Number(nip[i]) * weights[i]
  }

  const checksum = sum % 11

  return checksum !== 10 && checksum === Number(nip[9])
}
