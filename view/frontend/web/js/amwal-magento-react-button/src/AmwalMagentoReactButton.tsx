import React from 'react'
import { AmwalCheckoutButton } from 'amwal-checkout-button-react'
import { type IRefIdData, type IAmwalButtonConfig, type ISuccessInfo } from './IAmwalButtonConfig'
import { type AmwalCheckoutButtonCustomEvent, type IAddress, type IShippingMethod, type AmwalDismissalStatus, type AmwalCheckoutStatus, type ITransactionDetails } from 'amwal-checkout-button'
import { initAmwalSentry, isAmwalSentryEnabled, reportAmwalError, addAmwalBreadcrumb, startAmwalTransaction } from './sentry-utils';

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
  performSuccessRedirection?: (orderId: string, IncrementId: string) => void
  debug?: boolean
  applePayCheckout?: boolean
  paymentMethod?: string
  enableSentryTracking?: boolean
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
  debug,
  applePayCheckout,
  paymentMethod = 'amwal_payments',
  enableSentryTracking = true
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
  const [IncrementId, setIncrementId] = React.useState<string | undefined>(undefined)
  const [orderCompletionInProgress, setOrderCompletionInProgress] = React.useState(false)
  const [completedOrders, setCompletedOrders] = React.useState<Set<string>>(new Set())

  const paymentMethodLabels: Record<string, 'Quick Checkout' | 'Pay with Apple Pay' | 'Bank Installments'> = {
    amwal_payments: 'Quick Checkout',
    amwal_payments_apple_pay: 'Pay with Apple Pay',
    amwal_payments_bank_installments: 'Bank Installments'
  }

  const isSentryEnabled = enableSentryTracking && isAmwalSentryEnabled()
  
  const logError = React.useCallback((error: Error | string, context: string, additionalData?: Record<string, any>) => {
    // Always log to console
    console.error(`[Amwal ${context}]:`, error)

    // Log to Sentry if enabled (async but non-blocking)
    if (isSentryEnabled) {
      reportAmwalError(error, context, {
        triggerContext,
        locale,
        paymentMethod,
        hasCartId: !!cartId,
        hasProductId: !!productId,
        ...additionalData
      })
    }
  }, [isSentryEnabled, triggerContext, locale, paymentMethod, cartId, productId])

  // Helper function to handle API responses and check for 500 errors
  const handleApiResponse = React.useCallback(async (
    response: Response, 
    context: string, 
    additionalData?: Record<string, any>
  ): Promise<any> => {
    // Check for 5xx server errors and report to Sentry
    if (response.status >= 500 && response.status < 600) {
      const errorMessage = `Server Error ${response.status}: ${response.statusText}`
      const error = new Error(errorMessage)
      
      logError(error, `${context} - Server Error`, {
        statusCode: response.status,
        statusText: response.statusText,
        url: response.url,
        ...additionalData
      })
    }

    const data = await response.json()
    
    if (!response.ok) {
      throw new Error(data.message || data || response.statusText)
    }
    
    return data
  }, [logError])

  const applyButtonConfig = (data: IAmwalButtonConfig): void => {
    initAmwalSentry({
        dsn: "https://0352c5fdf6587d2cf2313bae5e3fa6fe@o4509389080690688.ingest.us.sentry.io/4509389509623808",
        environment: (data.test_environment && data.test_environment.trim()) ? 'development' : 'production'
    })
    setConfig(data)
    setAmount(data.amount)
    setDiscount(data.discount ?? 0)
    setTaxes(data.tax ?? 0)
    setFees(data.fees ?? 0)
    if (data.cart_id) setCartId(data.cart_id)
  }

  React.useEffect(() => {
    const initializeButton = async () => {
      const transaction = isSentryEnabled ? await startAmwalTransaction('Button Initialization', 'init') : { setStatus: () => {}, finish: () => {} };

      const initalRefIdData: IRefIdData = {
        identifier: '100',
        customer_id: '0',
        timestamp: Date.now()
      }
      setRefIdData(initalRefIdData)

      try {
        const response = await fetch(`${baseUrl}/amwal/button/cart`, {
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
        });

        const data = await handleApiResponse(response, 'Button Initialization', {
          baseUrl,
          triggerContext,
          hasProductId: !!productId
        })

        applyButtonConfig(data)
        transaction.setStatus('ok')

        if (isSentryEnabled) {
          addAmwalBreadcrumb('Button initialized successfully').catch(() => {})
        }
      } catch (err) {
        logError(err as Error, 'Button Initialization', {
          baseUrl,
          triggerContext,
          hasProductId: !!productId
        })
        transaction.setStatus('internal_error')
      } finally {
        transaction.finish()
      }
    };

    initializeButton()
  }, [])

  const getQuote = async (addressData?: IAddress): Promise<void> => {
    const transaction = isSentryEnabled ? await startAmwalTransaction('Get Quote', 'get_quote') : { setStatus: () => {}, finish: () => {} };

    try {
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

      const data = await handleApiResponse(response, 'Get Quote', {
        hasAddress: !!addressData,
        country: addressData?.country || 'unknown'
      })

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

        transaction.setStatus('ok')
        if (isSentryEnabled) {
          addAmwalBreadcrumb('Quote retrieved successfully', {
            shippingMethodsCount: Object.keys(quote.available_rates).length,
            hasAddress: !!addressData
          })
        }
        return quote
      }
      throw new Error(`Unexpected get-quote result ${JSON.stringify(data)}`)
    } catch (err) {
      transaction.setStatus('internal_error')
      throw err
    } finally {
      transaction.finish()
    }
  }

  const handleAmwalAddressUpdate = (event: AmwalCheckoutButtonCustomEvent<IAddress>): void => {
    getQuote(event.detail)
      .catch(err => {
        logError(err, 'Address Update', {
          hasAddressDetail: !!event.detail,
          country: event.detail?.country || 'unknown'
        })

        buttonRef.current?.dispatchEvent(new CustomEvent('amwalAddressTriggerError', {
          detail: {
            description: err?.toString(),
            error: err?.toString()
          }
        }))
      })
  }

  React.useEffect(() => {
    buttonRef.current?.dispatchEvent(new Event('amwalAddressAck'))
  }, [shippingMethods])

  const completeOrder = async (amwalOrderId: string): Promise<void> => {
    // Prevent duplicate calls by checking if this order is already being processed or completed
    if (orderCompletionInProgress || completedOrders.has(amwalOrderId)) {
      if (isSentryEnabled) {
        addAmwalBreadcrumb('Order completion skipped - already in progress or completed', {
          orderCompletionInProgress,
          hasAmwalOrderId: !!amwalOrderId,
          isAlreadyCompleted: completedOrders.has(amwalOrderId),
          transaction_id: amwalOrderId
        })
      }
      return
    }

    setOrderCompletionInProgress(true)
    const transaction = isSentryEnabled ? await startAmwalTransaction('Complete Order', 'complete_order', {
      transaction_id: amwalOrderId
    }) : { setStatus: () => {}, finish: () => {} };

    try {
      const response = await fetch(`${baseUrl}/amwal/pay-order`, {
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
      });

      await handleApiResponse(response, 'Complete Order', {
        hasOrderId: !!placedOrderId,
        hasAmwalOrderId: !!amwalOrderId,
        transaction_id: amwalOrderId
      })

      // Mark this order as completed to prevent future duplicate calls
      setCompletedOrders(prev => new Set(prev).add(amwalOrderId))

      transaction.setStatus('ok');
      window.dispatchEvent(new CustomEvent('cartUpdateNeeded'));

      if (onSuccessTask) {
        try {
          await onSuccessTask({ order_id: placedOrderId, amwal_transaction_id: amwalOrderId });
        } catch (err) {
          logError(err as Error, 'Success Task Callback', {
            hasOrderId: !!placedOrderId,
            hasAmwalOrderId: !!amwalOrderId,
            transaction_id: amwalOrderId
          });
        } finally {
          setFinishedUpdatingOrder(true);
        }
      } else {
        setFinishedUpdatingOrder(true);
      }
    } catch (err) {
      transaction.setStatus('internal_error');
      logError(err as Error, 'Complete Order Request', {
        hasOrderId: !!placedOrderId,
        hasAmwalOrderId: !!amwalOrderId,
        transaction_id: amwalOrderId
      });
      alert(`An error occurred while completing the order: ${(err as Error).toString()}`)
      // Dispatch error event to the button
      buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerError', {
        detail: {
          description: (err as Error)?.toString()
        }
      }))
    } finally {
      transaction.finish();
      setOrderCompletionInProgress(false)
    }
  }

  const handleAmwalDismissed = (event: AmwalCheckoutButtonCustomEvent<AmwalDismissalStatus>): void => {
    if (isSentryEnabled) {
      addAmwalBreadcrumb('Checkout dismissed', {
        paymentSuccessful: event.detail.paymentSuccessful,
        hasOrderId: !!event.detail.orderId,
        transaction_id: event.detail.orderId
      })
    }

    if (!event.detail.orderId) return

    if (event.detail.paymentSuccessful) {
      if (placedOrderId) {
        completeOrder(event.detail.orderId)
      }
    } else if (onCancelTask) {
      onCancelTask()
        .catch(err => {
          logError(err as Error, 'Cancel Task Callback', {
            transaction_id: event.detail.orderId
          })
        })
    } else if (emptyCartOnCancellation) {
      buttonRef.current?.setAttribute('disabled', 'true')
      fetch(`${baseUrl}/amwal/clean-quote`, {
        method: 'POST',
        headers: {
          ...extraHeaders,
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).then(async (response) => {
        // Handle 500 errors for clean-quote as well
        if (response.status >= 500 && response.status < 600) {
          logError(
            new Error(`Server Error ${response.status}: ${response.statusText}`), 
            'Clean Quote - Server Error',
            {
              statusCode: response.status,
              statusText: response.statusText,
              url: response.url,
              transaction_id: event.detail.orderId
            }
          )
        }
      }).catch(err => {
        logError(err as Error, 'Clean Quote Request', {
          transaction_id: event.detail.orderId
        })
      }).finally(() => {
        buttonRef.current?.removeAttribute('disabled')
        window.dispatchEvent(new CustomEvent('cartUpdateNeeded'))
      })
    }
  }

  const handleUpdateOrderOnPaymentsuccess = (event: AmwalCheckoutButtonCustomEvent<AmwalCheckoutStatus>): void => {
    completeOrder(event.detail.orderId)
  }

  const handleAmwalCheckoutSuccess = (event: AmwalCheckoutButtonCustomEvent<AmwalCheckoutStatus>): void => {
    if (isSentryEnabled) {
      addAmwalBreadcrumb('Checkout success received', {
        transaction_id: event.detail.orderId
      })
    }
    setReceivedSuccess(true) // coordinate with the updateOrderOnPaymentsuccess event
  }

  React.useEffect(() => {
    if (finishedUpdatingOrder && receivedSuccess) {
      if (placedOrderId && IncrementId) {
        buttonRef.current?.dismissModal().finally(() => {
          if (isSentryEnabled) {
            addAmwalBreadcrumb('Successful order completion and redirect', {
              hasPlacedOrderId: !!placedOrderId,
              hasIncrementId: !!IncrementId
            })
          }
          performSuccessRedirection(placedOrderId, IncrementId)
        })
      } else {
        const error = new Error('Unexpected state. placedOrderId is undefined after finished updating order and receiving success')
        logError(error, 'Order Completion State Error', {
          finishedUpdatingOrder,
          receivedSuccess,
          hasPlacedOrderId: !!placedOrderId,
          hasIncrementId: !!IncrementId
        })
      }
    }
  }, [finishedUpdatingOrder, receivedSuccess])

  const asyncHandleAmwalPrePayTrigger = async (event: AmwalCheckoutButtonCustomEvent<ITransactionDetails>): Promise<void> => {
    const transaction = isSentryEnabled ? await startAmwalTransaction('Pre-Pay Trigger', 'pre_pay', {
      transaction_id: event.detail.id
    }) : { setStatus: () => {}, finish: () => {} };

    try {
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
          has_amwal_address: !(triggerContext === 'regular-checkout'),
          card_bin: event.detail.card_bin,
          payment_method: paymentMethod
        })
      })

      const data = await handleApiResponse(response, 'Pre-Pay Trigger', {
        hasTransactionId: !!event.detail?.id,
        transactionId: event.detail?.id,
        paymentMethod,
        transaction_id: event.detail.id
      })

      setPlacedOrderId(data.entity_id)
      setIncrementId(data.increment_id)

      transaction.setStatus('ok')
      if (isSentryEnabled) {
        addAmwalBreadcrumb('Order placed successfully', {
          hasEntityId: !!data.entity_id,
          hasIncrementId: !!data.increment_id,
          paymentMethod,
          transaction_id: event.detail.id
        })
      }

      buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerAck', {
        detail: {
          order_id: data.entity_id,
          order_total_amount: data.total_due,
          ...(data.discount_description && { card_bin_additional_discount_message: data.discount_description }),
          ...(data.extension_attributes?.amwal_card_bin_additional_discount && { card_bin_additional_discount: data.extension_attributes.amwal_card_bin_additional_discount })
        }
      }))

    } catch (err) {
      transaction.setStatus('internal_error')
      throw err
    } finally {
      transaction.finish()
    }
  }

  const handleAmwalPrePayTrigger = (event: AmwalCheckoutButtonCustomEvent<ITransactionDetails>): void => {
    asyncHandleAmwalPrePayTrigger(event)
      .catch((err) => {
        logError(err, 'Pre-Pay Trigger', {
          hasTransactionId: !!event.detail?.id,
          transactionId: event.detail?.id,
          paymentMethod,
          transaction_id: event.detail.id
        })

        buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerError', {
          detail: {
            description: err?.toString()
          }
        }))
      })
  }

  const handleAmwalPreCheckoutTrigger = (_event: AmwalCheckoutButtonCustomEvent<ITransactionDetails>): void => {
    const handlePreCheckout = async () => {
      const transaction = isSentryEnabled ? await startAmwalTransaction('Pre-Checkout Trigger', 'pre_checkout') : { setStatus: () => {}, finish: () => {} };

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

      try {
        const preCheckoutPromise = (preCheckoutTask != null)
          ? preCheckoutTask().then(async (preCheckoutCartId?: string) => {
            const response = await getConfig(preCheckoutCartId)
            if (preCheckoutCartId) {
              setCartId(preCheckoutCartId)
            }
            return response
          })
          : getConfig()

        const response = await preCheckoutPromise;
        const data = await handleApiResponse(response, 'Pre-Checkout Trigger', {
          hasPreCheckoutTask: !!preCheckoutTask,
          triggerContext
        })

        applyButtonConfig(data)
        setTriggerPreCheckoutAck(true)
        transaction.setStatus('ok')
      } catch (err) {
        transaction.setStatus('internal_error')
        logError(err as Error, 'Pre-Checkout Trigger', {
          hasPreCheckoutTask: !!preCheckoutTask,
          triggerContext
        });

        buttonRef.current?.dispatchEvent(new CustomEvent('amwalPrePayTriggerError', {
          detail: {
            description: (err as Error)?.toString()
          }
        }));
      } finally {
        transaction.finish()
      }
    };

    handlePreCheckout()
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

  // Set user context for better debugging
  React.useEffect(() => {
    if (config && isSentryEnabled) {
      const setUserContext = async () => {
        try {
          // Dynamic import to avoid build issues
          const { setAmwalUserContext } = await import('./sentry-utils');
          await setAmwalUserContext({
            id: refIdData?.customer_id || 'anonymous',
            email: config.initial_email || undefined
          });
        } catch {
          // Sentry not available, ignore
        }
      };
      setUserContext()
    }
  }, [config, refIdData, isSentryEnabled])

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
        addressRequired={applePayCheckout ? false : config.address_required}
        refId={config.ref_id}
        label={paymentMethodLabels[paymentMethod] || 'Quick Checkout'}
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
        onlyShowBankInstallment={paymentMethod === 'amwal_payments_bank_installments'}
        enableAppleCheckout={applePayCheckout}
    />
    : <></>
}

export default AmwalMagentoReactButton;