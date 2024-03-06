import { type CountryCode } from 'libphonenumber-js'
import {
  type StateSpecs,
  type CitySpecs,
  type IAddress
} from 'amwal-checkout-button'

export interface IAmwalButtonConfig {
  merchant_id: string
  amount: number
  discount?: number
  fees?: number
  tax?: number
  country_code: CountryCode
  dark_mode: 'on' | 'off' | 'auto'
  email_required: boolean
  address_required: boolean
  address_handshake: boolean
  ref_id: string
  label: 'checkout' | 'quick-buy'
  disabled: boolean
  show_payment_brands: boolean
  enable_pre_checkout_trigger: boolean
  enable_pre_pay_trigger: boolean
  id: string
  test_environment: string
  allowed_address_states: Record<string, StateSpecs>
  allowed_address_cities: Record<string, CitySpecs>
  allowed_address_countries: string[]
  post_code_optional_countries: string[]
  initial_address: IAddress
  initial_email: string
  initial_phone: string
  plugin_version: string
  initial_first_name: string
  initial_last_name: string
  installment_options_url: string
  cart_id: string
  show_discount_ribbon: boolean
}

export interface IRefIdData {
  identifier: string
  customer_id: string
  timestamp: number
}

export interface ISuccessInfo {
  order_id?: string
  amwal_transaction_id: string
}
