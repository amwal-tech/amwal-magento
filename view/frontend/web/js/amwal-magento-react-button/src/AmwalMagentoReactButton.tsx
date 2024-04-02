import React from 'react'
import { AmwalCheckoutButton } from 'amwal-checkout-button-react'
import { type IRefIdData, type IAmwalButtonConfig, type ISuccessInfo } from './IAmwalButtonConfig'
import { type AmwalCheckoutButtonCustomEvent, type IAddress, type IShippingMethod, type AmwalDismissalStatus, type AmwalCheckoutStatus, type ITransactionDetails } from 'amwal-checkout-button'

interface AmwalMagentoReactButtonProps {
  triggerContext: string
  locale?: string
  scopeCode?: string
  productId?: string
  buttonId?: string
  preCheckoutTask?: () => Promise<string | undefined>
  onSuccessTask?: (Info: ISuccessInfo) => Promise<void>
  onCancelTask?: () => Promise<void>
  emptyCartOnCancellation?: boolean
  baseUrl?: string
  extraHeaders?: Record<string, string>
  overrideCartId?: string
  redirectURL?: string
  performSuccessRedirection?: (orderId: string) => void
  debug?: boolean
}

const AmwalMagentoReactButton = ({
  triggerContext,
  locale,
  scopeCode,
  productId,
  buttonId,
  preCheckoutTask,
  onSuccessTask,
  onCancelTask,
  emptyCartOnCancellation = triggerContext === 'product-listing-page' || triggerContext === 'product-detail-page' || triggerContext === 'product-list-widget' || triggerContext === 'amwal-widget',
  baseUrl = scopeCode ? `/rest/${scopeCode}/V1` : '/rest/V1',
  extraHeaders,
  overrideCartId,
  redirectURL = '/checkout/onepage/success',
  performSuccessRedirection = () => { window.location.href = redirectURL },
  debug
}: AmwalMagentoReactButtonProps): JSX.Element => {
  const buttonRef = React.useRef<HTMLAmwalCheckoutButtonElement>(null)
  const [config, setConfig] = React.useState<IAmwalButtonConfig | undefined>(undefined)
  const [amount, setAmount] = React.useState(0)
  const [taxes, setTaxes] = React.useState(0)
  const [discount, setDiscount] = React.useState(0)
  const [fees, setFees] = React.useState(0)
  const [feesDescription, setFeesDescription] = React.useState('')
  const [cartId, setCartId] = React.useState<string | undefined>(undefined)
  const [shippingMethods, setShippingMethods] = React.useState<IShippingMethod[]>([])
  const [placedOrderId, setPlacedOrderId] = React.useState<string | undefined>(undefined)
  const [finishedUpdatingOrder, setFinishedUpdatingOrder] = React.useState(false)
  const [receivedSuccess, setReceivedSuccess] = React.useState(false)
  const [refIdData, setRefIdData] = React.useState<IRefIdData | undefined>(undefined)
  const [triggerPreCheckoutAck, setTriggerPreCheckoutAck] = React.useState(false)

  const applyButtonConfig = (data: IAmwalButtonConfig): void => {
    setConfig(data)
    setAmount(data.amount)
    setDiscount(data.discount ?? 0)
    setTaxes(data.tax ?? 0)
    setFees(data.fees ?? 0)
    if (data.cart_id) setCartId(data.cart_id)
  }

  React.useEffect(() => {
    const initalRefIdData: IRefIdData = {
      identifier: '100',
      customer_id: '0',
      timestamp: Date.now()
    }
    setRefIdData(initalRefIdData)
    fetch(`${baseUrl}/amwal/button/cart`, {
      method: 'POST',
      headers: {
        ...extraHeaders,
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        refIdData: initalRefIdData,
        triggerContext,
        cartId: overrideCartId ?? cartId,
        productId,
        locale
      })
    })
      .then(async response => {
        const data = await response.json()
        if (!response.ok) throw new Error(data)
        return data
      })
      .then(data => { applyButtonConfig(data) })
      .catch(err => { console.error(err) })
  }, [])

  const getQuote = async (addressData?: IAddress): Promise<void> => {
    const response = await fetch(`${baseUrl}/amwal/get-quote`, {
      method: 'POST',
      headers: {
        ...extraHeaders,
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        ref_id: config?.ref_id,
        address_data: addressData,
        is_pre_checkout: false,
        trigger_context: triggerContext,
        ref_id_data: refIdData,
        order_items: [],
        cartId: overrideCartId ?? cartId,
        productId
      })
    })

    const data = await response.json()
    if (!response.ok) throw new Error(data.message ?? response.statusText)
    if (data instanceof Array && data.length > 0) {
      const quote = data[0]
      setCartId(quote.cart_id)
      const subtotal = parseFloat(quote.amount) -
            parseFloat(quote.tax_amount) -
            parseFloat(quote.shipping_amount) +
            parseFloat(quote.discount_amount)
      setAmount(subtotal)
      setTaxes(quote.tax_amount)
      setDiscount(quote.discount_amount)
      setFees(quote.additional_fee_amount)
      setFeesDescription(quote.additional_fee_description)
      setShippingMethods(Object.entries(quote.available_rates).map<IShippingMethod>(([id, rate]) => {
        return {
          id,
          label: (rate as any).carrier_title,
          price: (rate as any).price
        }
      }))
      return quote
    }
    throw new Error(`Unexpected get-quote result ${JSON.stringify(data)}`)
  }

  const handleAmwalAddressUpdate = (event: AmwalCheckoutButtonCustomEvent<IAddress>): void => {
    getQuote(event.detail)
      .catch(err => {
        buttonRef.current?.dispatchEvent(new CustomEvent('amwalAddressTriggerError', {
          detail: {
            description: err?.toString(),
            error: err?.toString()
          }
        }))
        console.error(err)
      })
  }

  React.useEffect(() => {
    buttonRef.current?.dispatchEvent(new Event('amwalAddressAck'))
  }, [shippingMethods])

  const completeOrder = (amwalOrderId: string): void => {
    fetch(`${baseUrl}/amwal/pay-order`, {
      method: 'POST',
      headers: {
        ...extraHeaders,
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        order_id: placedOrderId,
        amwal_order_id: amwalOrderId
      })
    })
      .then(response => {
        if (!response.ok) return
        window.dispatchEvent(new CustomEvent('cartUpdateNeeded'))
        if (onSuccessTask) {
          onSuccessTask({ order_id: placedOrderId, amwal_transaction_id: amwalOrderId })
            .catch(err => {
              console.error(err)
            })
            .finally(() => {
              setFinishedUpdatingOrder(true)
            })
        } else {
          setFinishedUpdatingOrder(true)
        }
      })
      .catch(err => {
        console.error(err)
      })
  }
  const handleAmwalDismissed = (event: AmwalCheckoutButtonCustomEvent<AmwalDismissalStatus>): void => {
    if (!event.detail.orderId) return
    if (event.detail.paymentSuccessful) {
      if (placedOrderId) {
        completeOrder(event.detail.orderId)
      }
    } else if (onCancelTask) {
      onCancelTask()
        .catch(err => {
          console.error(err)
        })
    } else if (emptyCartOnCancellation) {
      buttonRef.current?.setAttribute('disabled', 'true')
      fetch(`${baseUrl}/amwal/clean-quote`, {
        method: 'POST',
        headers: {
          ...extraHeaders,
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).finally(() => {
        buttonRef.current?.removeAttribute('disabled')
        window.dispatchEvent(new CustomEvent('cartUpdateNeeded'))
      })
    }
  }

  const handleUpdateOrderOnPaymentsuccess = (event: AmwalCheckoutButtonCustomEvent<AmwalCheckoutStatus>): void => {
    completeOrder(event.detail.orderId)
  }

  const handleAmwalCheckoutSuccess = (_event: AmwalCheckoutButtonCustomEvent<AmwalCheckoutStatus>): void => {
    setReceivedSuccess(true) // coordinate with the updateOrderOnPaymentsuccess event
  }

  React.useEffect(() => {
    if (finishedUpdatingOrder && receivedSuccess) {
      if (placedOrderId) {
        buttonRef.current?.dismissModal().finally(() => {
          performSuccessRedirection(placedOrderId)
        })
      } else {
        console.error('Unexpected state. placedOrderId is undefined after finished updating order and receiving success')
      }
    }
  }, [finishedUpdatingOrder, receivedSuccess])

  const asyncHandleAmwalPrePayTrigger = async (event: AmwalCheckoutButtonCustomEvent<ITransactionDetails>): Promise<void> => {
    const response = await fetch(`${baseUrl}/amwal/place-order`, {
      method: 'POST',
      headers: {
        ...extraHeaders,
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        ref_id: config?.ref_id,
        address_data: event.detail,
        cartId: overrideCartId ?? cartId,
        amwal_order_id: event.detail.id,
        ref_id_data: refIdData,
        trigger_context: triggerContext,
        has_amwal_address: !(triggerContext === 'regular-checkout')
      })
    })
    const data = await response.json()
    if (response.ok) {
      setPlacedOrderId(data.entity_id)
      buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerAck', {
        detail: {
          order_id: data.entity_id,
          order_total_amount: data.total_due
        }
      }))
    } else {
      buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerError', {
        detail: {
          description: data.message ?? data
        }
      }))
    }
  }

  const handleAmwalPrePayTrigger = (event: AmwalCheckoutButtonCustomEvent<ITransactionDetails>): void => {
    asyncHandleAmwalPrePayTrigger(event)
      .catch((err) => {
        console.error(err)
        buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerError', {
          detail: {
            description: err?.toString()
          }
        }))
      })
  }

  const handleAmwalPreCheckoutTrigger = (_event: AmwalCheckoutButtonCustomEvent<ITransactionDetails>): void => {
    const getConfig = async (preCheckoutCartId?: string): Promise<Response> => {
      return await fetch(`${baseUrl}/amwal/button/cart`, {
        method: 'POST',
        headers: {
          ...extraHeaders,
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          refIdData,
          triggerContext,
          locale,
          cartId: preCheckoutCartId ?? overrideCartId ?? cartId
        })
      })
    }
    const preCheckoutPromise = (preCheckoutTask != null)
      ? preCheckoutTask().then(async (preCheckoutCartId?: string) => {
        const response = await getConfig(preCheckoutCartId)
        if (preCheckoutCartId) {
          setCartId(preCheckoutCartId)
        }
        return response
      })
      : getConfig()
    preCheckoutPromise
      .then(async response => {
        const data = await response.json()
        if (!response.ok) throw new Error(data)
        return data
      })
      .then(data => {
        applyButtonConfig(data)
        setTriggerPreCheckoutAck(true)
      })
      .catch(err => {
        console.error(err)
        buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerError', {
          detail: {
            description: err?.toString()
          }
        }))
      })
  }

  React.useEffect(() => {
    if (triggerPreCheckoutAck) {
      buttonRef.current?.dispatchEvent(
        new CustomEvent('amwalPreCheckoutTriggerAck', {
          detail: {
            order_content: config?.order_content,
            order_position: triggerContext,
            plugin_version: 'Magento ' + (config?.plugin_version ?? '')
          }
        })
      )
      setTriggerPreCheckoutAck(false)
    }
  }, [triggerPreCheckoutAck])

  return (config != null)
    ? <AmwalCheckoutButton
        ref={buttonRef}
        id={buttonId}
        amount={amount}
        taxes={taxes}
        discount={discount}
        fees={fees}
        feesDescription={feesDescription}
        shippingMethods={shippingMethods}
        merchantId={config.merchant_id}
        countryCode={config.country_code}
        darkMode={config.dark_mode}
        emailRequired={config.email_required}
        addressRequired={config.address_required}
        refId={config.ref_id}
        label={config.label}
        showPaymentBrands={config.show_payment_brands}
        initialEmail={config.initial_email}
        initialPhoneNumber={config.initial_phone}
        initialAddress={config.initial_address}
        allowedAddressCities={config.allowed_address_cities}
        allowedAddressStates={config.allowed_address_states}
        allowedAddressCountries={JSON.stringify(config.allowed_address_countries) as any}
        test-environment={config.test_environment}
        addressHandshake={true}
        sendExtraAddressFields
        onAmwalAddressUpdate={handleAmwalAddressUpdate}
        onAmwalDismissed={handleAmwalDismissed}
        onAmwalCheckoutSuccess={handleAmwalCheckoutSuccess}
        enablePrePayTrigger={config.enable_pre_pay_trigger}
        onAmwalPrePayTrigger={handleAmwalPrePayTrigger}
        enablePreCheckoutTrigger={config.enable_pre_checkout_trigger}
        onAmwalPreCheckoutTrigger={handleAmwalPreCheckoutTrigger}
        onUpdateOrderOnPaymentsuccess={handleUpdateOrderOnPaymentsuccess}
        postcodeOptionalCountries={JSON.stringify(config.post_code_optional_countries) as any}
        initialFirstName={config.initial_first_name}
        initialLastName={config.initial_last_name}
        showDiscountRibbon={config.show_discount_ribbon}
        installmentOptionsUrl={config.installment_options_url}
        locale={locale}
        debug={debug}
    />
    : <></>
}

export default AmwalMagentoReactButton
