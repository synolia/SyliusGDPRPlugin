## Anonymization entity field configuration

### Customer :

- orders
- defaultAddress
- addresses
- user
- email
- emailCanonical
- firstName
- lastName
- birthday
- gender
- phoneNumber
- subscribedToNewsletter

### ShopUser :

- username
- usernameCanonical
- salt
- password
- lastLogin
- emailVerificationToken
- passwordResetToken
- passwordRequestedAt
- verifiedAt
- locked
- credentialsExpireAt
- oauthAccounts
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
- currencyCode
- localeCode
- customerIp
- notes

### Payments :

- details
- company
- countryCode
- street
- city
- postCode

To see the full configuration go to @SynoliaSyliusRGPDPlugin\Resources\config\mappings