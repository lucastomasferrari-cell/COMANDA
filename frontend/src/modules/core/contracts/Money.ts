export interface Money {
  amount: number
  formatted: string
  currency: string
  precision: number
}

export interface ConvertedMoney {
  original: Money
  converted: Money
  formatted?: string
}
