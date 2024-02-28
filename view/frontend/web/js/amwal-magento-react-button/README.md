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
        locale={locale}
        preCheckoutTask={submitAddToCart}
        extraHeaders={{
          'x-access-token': 'abc'
        }}
        buttonId="amwal-checkout-knmch"
        overrideCartId={cartId}
        redirectURL={redirectURL}
    />
    ...
}
```

## Properties
| Property                  | Type                                                     | Description                                                                                                   |
|---------------------------|----------------------------------------------------------|---------------------------------------------------------------------------------------------------------------|
| triggerContext            | string                                                   | Context where the button is instantiated. possible values `product-listing-page`, `regular-checkout`, .. etc. |
| locale                    | string                                                   | The locale that should be used for rendering the button. Default `en`.                                        |
| preCheckoutTask           | ()  =>   Promise < string \| undefined > \| undefined     | An asynchronous function that is fired when the button is clicked on checkout. Possible Uses are for performing operations such as adding product to cart if on product page. It can possibly return a new cartId to be used for checkout |
| onSuccessTask             | (Info: ISuccessInfo)  =>   Promise < void > \| undefined | An asynchronous function that is fired when a successful transaction happens |
| onCancelTask              | ()  =>   Promise < void > \| undefined                   | An asynchronous function that is fired when the users cancels |
| emptyCartOnCancellation   | boolean \| undefined                                     | controls behavior if Cart is emptied when the user cancels the checkout                                                                                                       |
| baseUrl                   | string \| undefined                                      | base URL for the magento backend. Defaults to `/rest/V1`                                                                                                                      |
| extraHeaders              | Record<string, string> \| undefined                      | extra headers in JSON format to send with fetch requests                                                                                                                      |
| overrideCartId            | string \| undefined                                      | Useful when you want to provide your own cartId. |
| redirectURL               | string \| undefined                                      | URL to redirect to after checkout is completed. Only effective if `performSuccessRedirection` is not set|
| performSuccessRedirection | (orderId: string)  =>  void \| undefined | A function that performs redirect on success, orderId has the magento order id |
| buttonId                  | string                                                   | The id of the button that will be rendered. This is useful when you want to have multiple buttons on the same page. |
| debug                     | boolean \| undefined                                     | Add debug option to button in console |
