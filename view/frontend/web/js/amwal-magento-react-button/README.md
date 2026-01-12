# Amwal Magento React Button

A React component for integrating Amwal checkout functionality with Magento. This component provides a seamless checkout experience that works in conjunction with the Amwal Magento API Backend.

## Prerequisites

Before using this component, ensure you have the [Amwal Magento Backend](https://docs.amwal.tech/docs/magento-installation) installed and configured.

## Installation

Install the React component in your project:

```bash
npm install @amwaljs/magento-react-button
```

## Basic Usage

### Import the Component

```javascript
import AmwalMagentoReactButton from '@amwaljs/magento-react-button'
```

### Product Page Implementation

For product pages, you'll need to handle adding items to cart before checkout:

```javascript
const ProductPage = () => {
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

  return (
    <AmwalMagentoReactButton
      triggerContext="product-page"
      locale="en"
      preCheckoutTask={submitAddToCart}
      extraHeaders={{
        'x-access-token': 'your-access-token'
      }}
      buttonId="amwal-checkout-product"
      redirectURL="/checkout/success"
    />
  )
}
```

### Cart/Mini-Cart Implementation

For cart or mini-cart pages, omit the `preCheckoutTask` since items are already in the cart:

```javascript
const CartPage = () => {
  return (
    <AmwalMagentoReactButton
      triggerContext="cart-page"
      locale="en"
      buttonId="amwal-checkout-cart"
      overrideCartId={cartId}
      redirectURL="/checkout/success"
    />
  )
}
```

## API Reference

### Component Properties

#### Required Properties

| Property | Type | Description |
|----------|------|-------------|
| `triggerContext` | `string` | Context where the button is instantiated. Values: `product-page`, `cart-page`, `mini-cart`, `regular-checkout`, etc. |
| `buttonId` | `string` | Unique identifier for the button element. Required when multiple buttons exist on the same page. |

#### Optional Properties

##### Localization & Configuration
| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `locale` | `string` | `"en"` | The locale for rendering the button text and interface. |
| `baseUrl` | `string` | `"/rest/V1"` | Base URL for the Magento backend API endpoints. |
| `extraHeaders` | `Record<string, string>` | `{}` | Additional headers to include in API requests (e.g., authentication tokens). |
| `debug` | `boolean` | `false` | Enable debug logging in the browser console. |

##### Cart & Checkout Behavior
| Property | Type | Description |
|----------|------|-------------|
| `overrideCartId` | `string` | Provide a specific cart ID instead of using the default session cart. |
| `emptyCartOnCancellation` | `boolean` | Whether to empty the cart when the user cancels checkout. |

##### Callback Functions
| Property | Type | Description |
|----------|------|-------------|
| `preCheckoutTask` | `() => Promise<string \| undefined>` | Async function executed before checkout (e.g., add to cart). Can return a new cart ID. |
| `onSuccessTask` | `(info: ISuccessInfo) => Promise<void>` | Async function executed after successful transaction. |
| `onCancelTask` | `() => Promise<void>` | Async function executed when user cancels checkout. |

##### Navigation & Redirects
| Property | Type | Description |
|----------|------|-------------|
| `redirectURL` | `string` | URL to redirect to after successful checkout (only if `performSuccessRedirection` is not provided). |
| `performSuccessRedirection` | `(orderId: string, incrementId: string) => void` | Custom function to handle post-checkout redirection with order details. |

## Advanced Usage

### Custom Success Handling

```javascript
const handleSuccess = async (info) => {
  console.log('Order completed:', info)
  // Custom success logic here
}

const handleCustomRedirect = (orderId, incrementId) => {
  window.location.href = `/order/success/${incrementId}`
}

<AmwalMagentoReactButton
  triggerContext="cart-page"
  buttonId="amwal-checkout-advanced"
  onSuccessTask={handleSuccess}
  performSuccessRedirection={handleCustomRedirect}
  debug={true}
/>
```

### With Authentication Headers

```javascript
<AmwalMagentoReactButton
  triggerContext="product-page"
  buttonId="amwal-checkout-auth"
  extraHeaders={{
    'Authorization': 'Bearer your-jwt-token',
    'X-Customer-ID': 'customer-id'
  }}
  baseUrl="/custom/api/v1"
/>
```

## TypeScript Support

This component is written in TypeScript and includes type definitions. The `ISuccessInfo` interface provides type safety for success callbacks:

```typescript
interface ISuccessInfo {
  orderId: string
  incrementId: string
  // Additional success information
}
```

## Troubleshooting

### Common Issues

#### Button Not Rendering
- Ensure the Amwal Magento Backend is properly installed and configured
- Check that the `buttonId` is unique on the page
- Verify API endpoints are accessible from your frontend

#### Checkout Not Working
- Confirm `triggerContext` matches your implementation context
- For product pages, ensure `preCheckoutTask` successfully adds items to cart
- Check browser console for errors when `debug` is enabled

#### Authentication Errors
- Verify `extraHeaders` contain valid authentication tokens
- Ensure your Magento backend accepts the provided headers
- Check API permissions for the authenticated user

### Debug Mode

Enable debug mode to get detailed console logging:

```javascript
<AmwalMagentoReactButton
  triggerContext="cart-page"
  buttonId="amwal-checkout-debug"
  debug={true}
/>
```

## Browser Compatibility

This component supports modern browsers with ES2017+ features:
- Chrome 60+
- Firefox 55+
- Safari 10.1+
- Edge 79+

## License

This component is licensed under the same terms as the Amwal Magento extension.

## Support

For support and documentation, visit [Amwal Documentation](https://docs.amwal.tech/) or contact the Amwal support team.
