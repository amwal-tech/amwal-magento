import React from 'react'
import { createRoot } from 'react-dom/client'
import AmwalMagentoReactButton from 'amwal-magento-react-button'

export const renderReactElement = (container: Element): void => {
  const triggerContext = container.getAttribute('data-trigger-context')
  const locale = container.getAttribute('data-locale')
  const scopeCode = container.getAttribute('data-scope-code')
  const productId = container.getAttribute('data-product-id')
  const buttonId = container.getAttribute('data-button-id')
  const formSelector = container.getAttribute('data-form-selector')
  const applePayCheckout = container.getAttribute('data-apple-pay-checkout')
  const paymentMethod = container.getAttribute('data-payment-method')
  const overrideCartId = container.getAttribute('data-override-cart-id')
  if (triggerContext) {
    const submitAddToCart = async (): Promise<string | undefined> => {
      if (!formSelector) return
      const cartForm = document.querySelector(formSelector)
      if (cartForm == null) throw new Error('Product form not found')
      const formURL = cartForm.getAttribute('action') ?? window.location.href
      if (!formURL) throw new Error('Product form URL not found')
      const response = await fetch(formURL, {
        method: 'POST',
        body: new FormData(cartForm as HTMLFormElement),
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      const data = await response.json()
      if (data.error) data.backUrl ? window.location.href = data.backUrl : window.location.reload()
    }
    const root = createRoot(container) // createRoot(container!) if you use TypeScript
    root.render(
        <AmwalMagentoReactButton
            triggerContext={triggerContext}
            locale={locale ?? undefined}
            scopeCode={scopeCode ?? undefined}
            productId={productId ?? undefined}
            buttonId={buttonId ?? undefined}
            overrideCartId={overrideCartId}
            applePayCheckout={applePayCheckout}
            paymentMethod={paymentMethod}
            preCheckoutTask={formSelector ? submitAddToCart : undefined}
        />)
  }
}

export const renderReactAll = (): void => {
  const containers = document.getElementsByClassName('amwal-express-checkout-button')
  Array.from(containers).forEach(renderReactElement)
}

declare global {
  interface Window {
    renderReactElement?: (container: Element) => void
    autoRenderReact?: boolean
  }
}

window.renderReactElement = renderReactElement

if (window.autoRenderReact) {
  renderReactAll()
}
