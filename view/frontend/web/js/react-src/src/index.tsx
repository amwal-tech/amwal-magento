import React from 'react'
import ReactDOM from 'react-dom'
import AmwalMagentoReactButton from './AmwalMagentoReactButton'

export const renderReactElement = (container: Element): void => {
  const triggerContext = container.getAttribute('trigger-context')
  if (triggerContext) {
    ReactDOM.render(<AmwalMagentoReactButton triggerContext={triggerContext}/>, container)
  }
}

export const renderReactAll = (): void => {
  const containers = document.getElementsByClassName('amwal-express-checkout-button')
  Array.from(containers).forEach(renderReactElement)
}

declare global {
  interface Window {
    renderReactElement?: (container: Element) => void
  }
}

window.renderReactElement = renderReactElement

renderReactAll()
