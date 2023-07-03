import { type CountryCode } from 'libphonenumber-js'
import { type StateSpecs, type CitySpecs, type IAddress } from 'amwal-checkout-button'

export interface IAmwalButtonConfig {
  merchant_id: string
  amount: number
  country_code: CountryCode
  locale: string
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
  initial_address: IAddress
  initial_email: string
  initial_phone: string
}
