## Exported entity field configuration

### Customer :

- user
- email
- emailCanonical
- defaultAddress
- addresses
- orders
- firstName
- lastName
- birthday
- gender
- createdAt
- updatedAt
- phoneNumber
- subscribedToNewsletter


### ShopUser :

- username
- usernameCanonical
- lastLogin
- verifiedAt
- email
- emailCanonical

### Address :

- firstName
- lastName
- phoneNumber
- company
- countryCode
- provinceCode
- provinceName
- street
- city
- postCode

### Order :

- shippingAddress
- billingAddress
- payments
- shipments
- currencyCode
- localeCode
- promotionCoupon
- checkoutState
- paymentState
- shippingState
- promotions
- customerIp
- checkoutCompletedAt
- number
- notes
- items
- itemsTotal
- adjustments
- adjustmentsTotal
- total
- state


### Payments :

- method
- currencyCode
- amount
- state
- details

### PaymentMethod :

- code

### Shipments :

- adjustments
- adjustmentsTotal
- state
- method
- units
- tracking
- shippedAt

### ShippingMethods :

- code

### Promotions :

- code
- name
- description

### OrderItems :

- productName
- variantName
- quantity
- unitPrice
- total
- unitsTotal
- adjustments
- adjustmentsTotal

### Adjustment :

- label
- amount

To see the full configuration go to @SynoliaSyliusRGPDPlugin\Resources\config\serialization