## New Payment Method: Google Pay (Scan QR / UPI)

### 1. Payment Option Description for Checkout Page:
**Name:** Google Pay (Scan QR / UPI)
**Description:** Pay instantly and securely using any UPI-enabled app. Simply scan the QR code or use our UPI ID to complete your purchase—no cash or card details needed.

### 2. Customer Checkout Flow (Post-Selection Instructions):

**Step 1: Complete Your Order**
* After filling in your shipping details, select "Google Pay (Scan QR / UPI)" as your payment method and click "Place Order."

**Step 2: Make Your Payment**
* You will be redirected to the Order Confirmation page, where you will find your Order ID and the payment details.
* **Option A: Scan QR Code**
    * Open your preferred UPI app (e.g., Google Pay, PhonePe, Paytm).
    * Tap the "Scan QR" option.
    * Scan the QR code displayed on the screen.
    * Enter the total order amount and complete the payment.
* **Option B: Pay via UPI ID**
    * Open your UPI app.
    * Select the "Pay to UPI ID" option.
    * Enter the UPI ID shown on the page: `your-business-upi-id@oksbi`.
    * Enter the total order amount and complete the payment.

**Step 3: Confirm Your Payment**
* After a successful payment, you will receive a Transaction ID (usually a 12-digit number) from your UPI app.
* Enter this Transaction ID in the "UPI Transaction ID" field on our website and click "Submit Transaction ID."
* Your order status will be updated, and we will begin processing it once the payment is verified.

### 3. Merchant Verification Protocol:
1.  **Receive Notification:** The merchant is notified of a new order with a "pending_verification" status.
2.  **Check Business Account:** The merchant logs into their Google Pay for Business account or associated bank account.
3.  **Match Transaction:** The merchant cross-references the incoming payments with the order details, using the customer-submitted Transaction ID and the order amount to find the corresponding payment.
4.  **Verify and Update:** Once the payment is confirmed, the merchant updates the order status from "pending_verification" to "processing" in the admin panel.
5.  **Process Order:** The order is now ready for fulfillment.

### 4. Suggested UI/UX Elements:
*   **Checkout Page:**
    *   A radio button with the label "Google Pay (Scan QR / UPI)."
    *   A short description explaining the process.
    *   Google Pay logo for visual recognition.
*   **Confirmation Page:**
    *   A clear display of the Order ID.
    *   A prominent, scannable QR code image.
    *   The UPI ID displayed as text with a "Copy" button.
    *   An input field for the customer to enter their UPI Transaction ID.
    *   A "Submit" button to confirm the transaction ID entry.

### 5. Potential Challenges & Future Solutions:
*   **Challenge:** The manual verification process is time-consuming and prone to human error, which can lead to delays in order processing. It also relies on the customer correctly entering the Transaction ID.
*   **Future Solution:** Integrate a payment gateway API (like Razorpay or Stripe) that supports UPI payments. This would automate the entire payment verification process, providing instant confirmation to both the customer and the merchant, and eliminating the need for manual transaction ID submission.