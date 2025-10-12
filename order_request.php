<?php
session_start();
require __DIR__ . '/includes/db_connection.php';

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Invalid request method.");
}

$listingId = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;
$conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;

$sql = "SELECT l.listings_id, l.name, l.price, li.image_url
        FROM listings l
        LEFT JOIN listing_images li 
          ON l.listings_id = li.listings_id
        WHERE l.listings_id = :id
        ORDER BY li.is_primary DESC, li.image_id ASC
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $listingId]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

$sql2 = "SELECT current_offer_amount
        FROM conversation
        WHERE conversation_id = :conversation_id";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute(['conversation_id' => $conversationId]);
$conversation = $stmt2->fetch(PDO::FETCH_ASSOC);

$sql3 = "SELECT * 
        FROM shipping_address 
        WHERE user_id = :user_id
        ORDER BY is_default DESC, date_modified DESC";
$stmt3 = $pdo->prepare($sql3);
$stmt3->execute(['user_id' => $_SESSION['user_id']]);
$shippingAddresses = $stmt3->fetchAll(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Listing not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Conversation | Michaelite Store</title>
  <link rel="stylesheet" href="/public/css/style.css">
  <link rel="stylesheet" href="/public/css/component.css">
  <script src="https://www.paypal.com/sdk/js?client-id=AW4wJ5P1mLbEAB2xa3ROXCqpqcyk7w3eaYr5AEolesAP25Nt7LJ22tajIMSI_iLa82Wgl_McPhWj3S5x&currency=PHP&components=buttons&locale=en_PH"></script>
  <style>
    body {display: flex; justify-content: space-between; align-items: center; flex-direction: column; gap: 2rem}
  </style>
</head>
<body>
<header>
    <?php include './public/components/header.php' ?>
</header>
  <div class="order-summary-container">
    <h1>Order Request</h1>

    <div class="order-summary">
      <img src="./public/assets/images/products/<?=htmlspecialchars($listing['image_url']) ?>" alt="Product thumbnail">
      <div>
        <h2><?= htmlspecialchars($listing['name']) ?></h2>
        <p>Price: PHP <?= number_format($conversation['current_offer_amount'], 2) ?></p>
      </div>
    </div>

    <form action="place_order.php" method="post">
      <input type="hidden" name="listing_id" value="<?= $listing['listings_id'] ?>">

      <div class="order-form-section">
        <label for="delivery">Delivery Method</label>
        <select name="delivery" id="delivery" required>
          <option value="">-- Select Delivery Method --</option>
          <option selected value="ship">Ship to Address</option>
          <option value="rider">Local Rider Drop</option>
          <option value="meetup">Meet Up</option>
        </select>
      </div>

      <div id="shipping-section">
        <div class="order-form-section">
          <label for="shipping_address_id">Shipping Address</label>
          <select name="shipping_address_id" id="shipping_address_id" required>
            <option value="">-- Select Address --</option>
            <?php foreach ($shippingAddresses as $addr): ?>
              <option selected value="<?= $addr['shipping_address_id'] ?>"
                <?= $addr['is_default'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($addr['street_address'] . ', ' . $addr['barangay'] . ', ' . $addr['city'] . ', ' . $addr['province']) ?>
                (<?= htmlspecialchars($addr['type']) ?>)
              </option>
            <?php endforeach; ?>
            <option value="new">+ Add New Address</option>
          </select>
        </div>

        <div id="new-address-form" style="display:none; margin-top:1rem;">
          <label>Street Address</label>
          <input type="text" name="new_street_address">

          <label>Barangay</label>
          <input type="text" name="new_barangay">

          <label>City</label>
          <input type="text" name="new_city" value="Iligan City">

          <label>Province</label>
          <input type="text" name="new_province" value="Lanao del Norte">

          <label>Type</label>
          <select name="new_type">
            <option value="home">Home</option>
            <option value="work">Work</option>
            <option value="pick up">Pick Up</option>
          </select>
        </div>
      </div>

      <div id="new-address-form" style="display:none; margin-top:1rem;">
        <label>Street Address</label>
        <input type="text" name="new_street_address">

        <label>Barangay</label>
        <input type="text" name="new_barangay">

        <label>City</label>
        <input type="text" name="new_city" value="Iligan City">

        <label>Province</label>
        <input type="text" name="new_province" value="Lanao del Norte">

        <label>Type</label>
        <select name="new_type">
          <option value="home">Home</option>
          <option value="work">Work</option>
          <option value="pick up">Pick Up</option>
        </select>
      </div>

      <div class="order-form-section">
        <label for="payment">Payment Method</label>
        <select name="payment" id="payment" required>
          <option value="">-- Select Payment --</option>
          <option selected="selected" value="cod">Cash on Delivery</option>
          <option value="Paypal">Paypal</option>
        </select>
      </div>

      <div class="order-details-price">
        <div class="order-details-price-subtotal">
          <p>Product Subtotal</p>
          <p>PHP <?= number_format($conversation['current_offer_amount'], 2) ?></p>
        </div>
        <div class="order-details-price-subtotal">
          <p>Shipping Subtotal</p>
          <p id="shipping-price-display">PHP 0</p>
        </div>
        <div class="order-details-price-subtotal">
          <p>Total Payment</p>
          <p id="order-total-display">PHP <?= number_format($conversation['current_offer_amount'], 2) ?></p>
        </div>
      </div>
      
            <br>
      <button id="place-order-id" type="submit" class="order-place-order-btn chat-btn">Place Order</button>
      
    </form>

    <div id="paypal-button-container"></div>
  </div>
  
</body>
<footer>
    <?php include './public/components/footer.php'  ?>
</footer>

<script>
  let total = 0;

  const deliverySelect = document.getElementById('delivery');
  const shippingSection = document.getElementById('shipping-section');
  const shippingDropdown = document.getElementById('shipping_address_id');
  const newAddressForm = document.getElementById('new-address-form');
  const subtotalEl = document.getElementById('shipping-price-display');
  const totalEl = document.getElementById('order-total-display');
  const placeOrderbtn = document.getElementById('place-order-id');
  const paymentSelect = document.getElementById('payment');
  const paypalContainer = document.getElementById('paypal-button-container');

  const subtotal = parseFloat("<?= number_format($conversation['current_offer_amount'], 2, '.', '') ?>");
  const listingId = "<?= $listing['listings_id'] ?>";

  shippingDropdown.addEventListener('change', function () {
    newAddressForm.style.display = this.value === 'new' ? 'block' : 'none';
    updatePayPalButtonState();
  });

  function updateShippingVisibility() {
    const method = deliverySelect.value;
    const needsShipping = method !== 'meetup';

    shippingSection.style.display = needsShipping ? 'block' : 'none';

    if (!needsShipping) {
      shippingDropdown.value = '';
      newAddressForm.style.display = 'none';
    }

    updateShippingAndTotal();
    updatePayPalButtonState();
  }

  deliverySelect.addEventListener('change', updateShippingVisibility);

  function updateShippingAndTotal() {
    const method = deliverySelect.value;
    const shipping = (method === 'meetup' || method === '') ? 0 : 150;
    const total = subtotal + shipping;

    subtotalEl.textContent = `PHP ${shipping.toFixed(2)}`;
    totalEl.textContent = `PHP ${total.toFixed(2)}`;
  }

  deliverySelect.addEventListener('change', updateShippingAndTotal);
  updateShippingAndTotal();

  function isFormValid() {
    const method = deliverySelect.value;
    const payment = paymentSelect.value;
    const shippingRequired = method !== 'meetup';

    const shippingValid = !shippingRequired || shippingDropdown.value !== '';
    const newAddressVisible = newAddressForm.style.display === 'block';
    const newAddressFilled = !newAddressVisible || (
      document.getElementById('new-address-line')?.value.trim() !== '' &&
      document.getElementById('new-city')?.value.trim() !== '' &&
      document.getElementById('new-postal')?.value.trim() !== ''
    );

    return payment === 'Paypal' && shippingValid && newAddressFilled;
  }

  function updatePayPalButtonState() {
    paypalContainer.style.display = isFormValid() ? 'block' : 'none';
  }

  paymentSelect.addEventListener('change', function () {
    if (this.value === 'Paypal') {

      placeOrderbtn.style.display = 'none';
      paypalContainer.innerHTML = '';

      paypal.Buttons({
        style: {
          color: 'black'
        },
        createOrder: function () {
          const method = deliverySelect.value;
          const shipping = (method === 'meetup' || method === '') ? 0 : 150;
          const calculatedTotal = subtotal + shipping;
          const addressId = shippingDropdown;
          function saveNewAddress() {
            const isNew = document.getElementById('shipping_address_id').value === 'new';
            if (!isNew) return;

            const addressData = {
              street: document.getElementById('new_street_address').value.trim(),
              barangay: document.getElementById('new_barangay').value.trim(),
              city: document.getElementById('new_city').value.trim(),
              province: document.getElementById('new_province').value.trim(),
              type: document.getElementById('new_type').value,
              user_id: <?= $_SESSION['user_id'] ?>
            };

            fetch('/includes/save_new_address.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(addressData)
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                  const newAddressId = data.address_id;
                  addressId = newAddressId;
              } else {
                console.error('Address save failed:', data.message);
              }
            });
          }

          return fetch("/includes/create_paypal_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              listing_id: listingId,
              amount: calculatedTotal.toFixed(2),
              currency: "PHP",
              shipping_preference: "NO_SHIPPING",
              locale: "en-PH",
              shippingAddressId: addressId,
              listingName: "<?= htmlspecialchars($listing['name']) ?>"
            })
          })
            .then(res => res.json())
            .then(data => {
              const orderId = data.orderID;
              if (!orderId) throw new Error("No order ID returned");
              return orderId;
            });
        },

        onApprove: function (data, actions) {
          return fetch("/includes/capture_paypal_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ orderID: data.orderID })
          })
          .then(res => res.json())
          .then(response => {
            console.log("Capture response:", response);
            const listingId = <?= json_encode($listing['listings_id']) ?>;
            const delivery = document.getElementById('delivery').value;
            const addressId = document.getElementById('shipping_address_id').value;
            const paymentMethod = 'PayPal';
            const paypalOrderId = response.details.id;

            return fetch("/place_order.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                listing_id: listingId,
                delivery: delivery,
                shipping_address_id: addressId,
                payment_method: paymentMethod,
                paypal_order_id: paypalOrderId,
                payment: "Paypal"
              })
            });
          })
          .then(res => res.json())
          .then(orderResponse => {
            if (orderResponse.status === 'success' && orderResponse.order_id) {
              window.location.href = `/order_success.php?order_id=${orderResponse.order_id}`;
            } else {
              alert("Order creation failed. Please try again.");
            }
          });
        }
      }).render("#paypal-button-container");

    } else {
      paypalContainer.style.display = 'none';
      placeOrderbtn.style.display = 'block';
    }

    updatePayPalButtonState();
  });

  ['new-address-line', 'new-city', 'new-postal'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', updatePayPalButtonState);
  });

  updatePayPalButtonState();
</script>


</html>
