import React from 'react'
import { AmwalCheckoutButton } from 'amwal-checkout-button-react'
import { type IAmwalButtonConfig } from './IAmwalButtonConfig'

const CartButton = (): JSX.Element => {
  const [config, setConfig] = React.useState<IAmwalButtonConfig | undefined>(undefined)

  React.useEffect(() => {
    fetch('/rest/V1/amwal/button/cart', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        refIdData: {
          identifier: '100',
          customer_id: '0',
          timestamp: Date.now()
        }
      })
    })
      .then(async response => await response.json())
      .then(data => { setConfig(data) })
      .catch(err => { console.log(err) })
  }, [])

  return config
    ? <AmwalCheckoutButton
        id={config.id}
        amount={config.amount}
        merchantId={config.merchant_id}
        countryCode={config.country_code}
        locale={config.locale}
        darkMode={config.dark_mode}
        emailRequired={config.email_required}
        addressRequired={config.address_required}
        addressHandshake={config.address_handshake}
        refId={config.ref_id}
        label={config.label}
        disabled={config.disabled}
        showPaymentBrands={config.show_payment_brands}
        enablePreCheckoutTrigger={config.enable_pre_checkout_trigger}
        enablePrePayTrigger={config.enable_pre_pay_trigger}
        initialEmail={config.initial_email}
        initialPhoneNumber={config.initial_phone}
        initialAddress={config.initial_address}
        allowedAddressCities={config.allowed_address_cities}
        allowedAddressStates={config.allowed_address_states}
        test-environment={config.test_environment}
    />
    : <></>
}

export default CartButton
