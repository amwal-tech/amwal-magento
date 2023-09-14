import React from 'react'
import { createRoot } from 'react-dom/client'
import AmwalMagentoReactButton from 'amwal-magento-react-button'

export const renderReactElement = (container: Element): void => {
  const triggerContext = container.getAttribute('trigger-context')
  const formSelector = container.getAttribute('form-selector')
  if (triggerContext) {
    const submitAddToCart = async (): Promise<void> => {
      if (!formSelector) return
      const cartForm = document.querySelector(formSelector)
      if (cartForm == null) throw new Error('Product form not found')
      const formURL = cartForm.getAttribute('action') ?? window.location.href
      if (!formURL) throw new Error('Product form URL not found')
      await fetch(formURL, {
        method: 'POST',
        body: new FormData(cartForm as HTMLFormElement),
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
    }
    const root = createRoot(container) // createRoot(container!) if you use TypeScript
    root.render(
        <AmwalMagentoReactButton
            triggerContext={triggerContext}
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
