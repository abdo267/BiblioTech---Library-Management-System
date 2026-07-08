<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$memberId = Auth::getMemberId();
$cartModel = new Cart();
$cartItems = $cartModel->getItemsByMember($memberId);
$total = $cartModel->getTotal($memberId);

if (empty($cartItems)) {
    Session::setFlash('error', 'Your cart is empty.');
    header('Location: ' . BASE_URL . '/index.php?route=member/cart');
    exit;
}

// Check configuration
$stripePublishableKey = STRIPE_PUBLISHABLE_KEY;
$isRealStripe = $stripePublishableKey !== '' && !str_starts_with($stripePublishableKey, 'pk_test_YOUR');
?>

<div class="container py-5 fade-in-up">
 
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=member/cart">Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Billing Details / Payment Method-->
        <div class="col-lg-7">
            <div class="card card-custom border-0 shadow-sm p-4">
                <h3 class="fw-bold mb-4 pb-2 border-bottom">
                    <i class="fa-solid fa-shield-halved text-success me-2"></i>Secure Checkout
                </h3>

                <!-- Alert Message -->
                <div id="payment-message" class="alert d-none"></div>

                <!-- Dual Checkout Container -->
                <?php if ($isRealStripe): ?>
                    <!--Stripe Elements Checkout -->
                    <form id="payment-form" class="mt-2">
                        <h5 class="fw-bold mb-3"><i class="fa-regular fa-credit-card me-2"></i>Card Details</h5>
                        <div class="mb-4">
                            <label class="form-label small text-muted">Credit or Debit Card</label>
                            <div id="payment-element" class="p-3 border rounded-3 bg-light-custom" style="min-height: 50px;">
                                
                            </div>
                        </div>

                        <button id="submit" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm" style="background: var(--primary-gradient); border: none;">
                            <span id="button-text">Pay EGP <?php echo number_format($total, 2); ?></span>
                            <span id="spinner" class="spinner-border spinner-border-sm d-none ms-2"></span>
                        </button>
                    </form>
                <?php else: ?>
                    <!--Stripe Simulator Sandbox Fallback -->
                    <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-3 p-3 rounded-3" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af;">
                        <i class="fa-solid fa-circle-info fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Stripe Sandbox Mode Active</h6>
                            <p class="mb-0 small">No valid API keys configured. Using built-in local Stripe Checkout Simulator.</p>
                        </div>
                    </div>

                    <form id="sim-payment-form" action="<?php echo BASE_URL; ?>/index.php?action=checkout-confirm" method="POST" class="mt-2">
                        <input type="hidden" name="payment_intent_id" id="sim-intent-id" value="">
                        
                        <h5 class="fw-bold mb-3"><i class="fa-regular fa-credit-card me-2"></i>Simulated Card Payment</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label small text-muted">Cardholder Name</label>
                                <input type="text" class="form-control form-control-custom" id="sim-cardname" placeholder="John Doe" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Card Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-credit-card text-muted"></i></span>
                                    <input type="text" class="form-control form-control-custom" id="sim-cardnumber" placeholder="4242 4242 4242 4242" required maxlength="19">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Expiration Date</label>
                                <input type="text" class="form-control form-control-custom" id="sim-cardexpiry" placeholder="MM/YY" required maxlength="5">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">CVC</label>
                                <input type="text" class="form-control form-control-custom" id="sim-cardcvc" placeholder="123" required maxlength="3">
                            </div>
                        </div>

                        <button type="submit" id="sim-submit-btn" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm" style="background: var(--primary-gradient); border: none;">
                            <span id="sim-button-text">Pay EGP <?php echo number_format($total, 2); ?></span>
                            <span id="sim-spinner" class="spinner-border spinner-border-sm d-none ms-2"></span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Summary Detail-->
        <div class="col-lg-5">
            <div class="card card-custom border-0 shadow-sm p-4 bg-light-custom">
                <h4 class="fw-bold mb-4 border-bottom pb-2">Items to Pay</h4>

                <div class="mb-4" style="max-height: 250px; overflow-y: auto;">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                            <?php if (!empty($item['cover_image']) && file_exists(UPLOAD_PATH . '/' . $item['cover_image'])): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $item['cover_image']; ?>" class="rounded shadow-sm" style="width: 35px; height: 48px; object-fit: cover;" alt="">
                            <?php elseif (!empty($item['cover_image']) && strpos($item['cover_image'], 'http') === 0): ?>
                                <img src="<?php echo $item['cover_image']; ?>" class="rounded shadow-sm" style="width: 35px; height: 48px; object-fit: cover;" alt="">
                            <?php else: ?>
                                <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 48px;"><i class="fa-solid fa-image small" style="font-size: 0.6rem;"></i></div>
                            <?php endif; ?>
                            <div class="flex-grow-1 min-width-0">
                                <span class="fw-bold text-dark-theme-override d-block text-truncate" style="font-size: 0.9rem; color: var(--text-color);"><?php echo htmlspecialchars($item['book_title']); ?></span>
                                <span class="text-muted small">Overdue library fine</span>
                            </div>
                            <span class="text-danger fw-bold">EGP <?php echo number_format($item['fine_amount'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Fines Subtotal</span>
                    <span class="text-dark-theme-override" style="color: var(--text-color);">EGP <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold text-dark-theme-override" style="color: var(--text-color);">Total Due</span>
                    <span class="fw-bold text-primary fs-4">EGP <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Stripe Integration Javascript -->
<?php if ($isRealStripe): ?>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const stripe = Stripe("<?php echo $stripePublishableKey; ?>");
            let elements;

        
            try {
                const response = await fetch("<?php echo BASE_URL; ?>/index.php?action=create-payment-intent");
                const data = await response.json();
                
                if (data.error) {
                    showErrorMessage(data.error);
                    return;
                }

                const options = {
                    clientSecret: data.clientSecret,
                    appearance: {
                        theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'flat',
                        variables: {
                            colorPrimary: '#4f46e5',
                            colorBackground: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff',
                            colorText: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b',
                        }
                    }
                };

                elements = stripe.elements(options);
                const paymentElement = elements.create("payment");
                paymentElement.mount("#payment-element");
            } catch (e) {
                showErrorMessage("Could not initialize Stripe elements. Please try again.");
            }

            const form = document.getElementById("payment-form");
            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                setLoading(true);

                const { error, paymentIntent } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: "<?php echo BASE_URL; ?>/index.php?action=checkout-confirm",
                    },
                    redirect: "if_required"
                });

                if (error) {
                    showErrorMessage(error.message);
                    setLoading(false);
                } else if (paymentIntent && paymentIntent.status === "succeeded") {
                    // Submit confirmed payment intent to server post-route to confirm db updates
                    const postForm = document.createElement("form");
                    postForm.method = "POST";
                    postForm.action = "<?php echo BASE_URL; ?>/index.php?action=checkout-confirm";
                    
                    const piInput = document.createElement("input");
                    piInput.type = "hidden";
                    piInput.name = "payment_intent_id";
                    piInput.value = paymentIntent.id;
                    
                    postForm.appendChild(piInput);
                    document.body.appendChild(postForm);
                    postForm.submit();
                }
            });

            function showErrorMessage(messageText) {
                const messageContainer = document.querySelector("#payment-message");
                messageContainer.classList.remove("d-none", "alert-success");
                messageContainer.classList.add("alert-danger");
                messageContainer.innerText = messageText;
            }

            function setLoading(isLoading) {
                if (isLoading) {
                    document.querySelector("#submit").disabled = true;
                    document.querySelector("#spinner").classList.remove("d-none");
                    document.querySelector("#button-text").innerText = "Processing...";
                } else {
                    document.querySelector("#submit").disabled = false;
                    document.querySelector("#spinner").classList.add("d-none");
                    document.querySelector("#button-text").innerText = "Pay EGP <?php echo number_format($total, 2); ?>";
                }
            }
        });
    </script>
<?php else: ?>
    <!-- Simulated Stripe Checkout Sandbox Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById("sim-payment-form");
            const btn = document.getElementById("sim-submit-btn");
            const spinner = document.getElementById("sim-spinner");
            const btnText = document.getElementById("sim-button-text");
            const cardNum = document.getElementById("sim-cardnumber");
            const cardExp = document.getElementById("sim-cardexpiry");
            const cardCvc = document.getElementById("sim-cardcvc");

            // Format card number
            cardNum.addEventListener('input', (e) => {
                let v = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let matches = v.match(/\d{4,16}/g);
                let match = matches && matches[0] || '';
                let parts = [];

                for (let i=0, len=match.length; i<len; i+=4) {
                    parts.push(match.substring(i, i+4));
                }

                if (parts.length > 0) {
                    e.target.value = parts.join(' ');
                } else {
                    e.target.value = v;
                }
            });

            // Format expiry date
            cardExp.addEventListener('input', (e) => {
                let v = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                if (v.length >= 2) {
                    e.target.value = v.substring(0, 2) + '/' + v.substring(2, 4);
                } else {
                    e.target.value = v;
                }
            });

            // Format CVC
            cardCvc.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            });

            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                
                btn.disabled = true;
                spinner.classList.remove("d-none");
                btnText.innerText = "Processing Simulated Fines...";

                
                document.getElementById("sim-intent-id").value = "pi_sim_" + Math.random().toString(36).substr(2, 12);

                
                setTimeout(() => {
                    form.submit();
                }, 1500);
            });
        });
    </script>
<?php endif; ?>
