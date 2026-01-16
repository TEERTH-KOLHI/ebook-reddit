# Testing with Fake Credentials

To test the payment flow, you cannot use "random" characters for the API Keys (Key ID and Secret) because the backend tries to create a real order on Razorpay's servers.

However, you **SHOULD** use Razorpay's **Test Mode**.

## 1. Get Valid Test API Keys
1. Log in to your [Razorpay Dashboard](https://dashboard.razorpay.com/).
2. Toggle the mode in the top right corner to **Test Mode**.
3. Go to **Settings** -> **API Keys**.
4. Click **Generate Key**.
5. Copy the **Key ID** (starts with `rzp_test_...`) and **Key Secret**.
6. Paste them into your `config.php` file.

## 2. Testing Inputs
- **Phone Field**: Try typing letters (e.g., `abc`). They should **NOT** appear. Only numbers are allowed.

## 3. Use Fake Payment Details
Once the keys are set up and the payment modal opens, use these details to make a successful "fake" payment:

| Field | Value |
| :--- | :--- |
| **Card Number** | `4111 1111 1111 1111` |
| **Expiry Date** | Any future date (e.g., `12/30`) |
| **CVV** | Any 3 digits (e.g., `123`) |
| **OTP** | `123456` |

**Note**: No money will be deducted in Test Mode.
