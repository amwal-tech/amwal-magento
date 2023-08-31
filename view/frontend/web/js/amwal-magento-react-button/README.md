# amwal-magento-react-button

This is a react component for Amwal Checkout button working in conjunction with an installed Amwal Magento API Backend

## Installation.

1. [Install Amwal Magento Backend](https://docs.amwal.tech/docs/magento-installation)


2. Install react component into your react project
```bash
npm i amwal-magento-react-button
```
## Usage

```javascript
import AmwalMagentoReactButton from 'amwal-magento-react-button'

const ReactPage= () => {
    ...
    // add item to cart if the button is on the product page. when used in cart or mini-cart, preCheckoutTask should be left undefined
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

    <AmwalMagentoReactButton
        triggerContext={triggerContext}
        preCheckoutTask={submitAddToCart}
        extraHeaders={{
          'x-access-token': 'abc'
        }}
    />
    ...
}
```

## Properties
| Property                | Type                                  | Description                                                                                                                                                                   |
|-------------------------|---------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| triggerContext          | string                                | Context where the button is instantiated. possible values `product-listing-page`, `regular-checkout`, .. etc.                                                                                                                    |
| preCheckoutTask         | ()  =>   Promise < void >\| undefined | An asynchronous function that is fired when the button is clicked on checkout. Possible Uses are for performing operations such as adding product to cart if on product page. |
| emptyCartOnCancellation | boolean \| undefined                  | controls behavior if Cart is emptied when the user cancels the checkout                                                                                                       |
| baseUrl | string \| undefined                  | base URL for the magento backend. Defaults to `/rest/V1`                                                                                                       |
| extraHeaders | Record<string, string> \| undefined                  | extra headers in JSON format to send with fetch requests                                                                                                      |