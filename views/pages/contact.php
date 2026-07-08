<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

// Check for simulated form submit
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    
    $validator = new Validator();
    $rules = [
        'name' => ['required' => true, 'min' => 3],
        'email' => ['required' => true, 'email' => true],
        'message' => ['required' => true, 'min' => 10]
    ];
    
    if ($validator->validate($_POST, $rules)) {
        
        EmailSim::send('librarian@bibliotech.com', "Contact Query from {$fullName}", "Name: {$fullName}\nEmail: {$email}\nMessage:\n{$message}");
        Session::setFlash('success', 'Your inquiry has been simulated and saved to email log! We will contact you soon.');
        header('Location: ' . BASE_URL . '/index.php?route=contact');
        exit;
    } else {
        Session::setFlash('error', $validator->getFirstError());
    }
}
?>

<div class="container py-5 fade-in-up">
  
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
        </ol>
    </nav>

    <div class="text-center mb-5">
        <h1 class="fw-extrabold display-5">Get in Touch</h1>
        <p class="text-muted col-lg-8 mx-auto lead">Have questions about registration, fines, or catalog donations? Drop us a message, and our librarian team will reply within 24 hours.</p>
    </div>

    <div class="row g-5">
        <!-- Contact Information Blocks -->
        <div class="col-lg-5">
            <h3 class="fw-bold mb-4">Contact Information</h3>
            
            <div class="d-flex flex-column gap-4">
                <div class="card card-custom border-0 shadow-sm p-3 glass-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-3 text-primary bg-primary-subtle rounded p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Our Location</h6>
                            <p class="text-muted small mb-0">123 Education Ave, Cairo, Egypt</p>
                        </div>
                    </div>
                </div>
                
                <div class="card card-custom border-0 shadow-sm p-3 glass-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-3 text-success bg-success-subtle rounded p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-phone-volume"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Call Us</h6>
                            <p class="text-muted small mb-0">+20 123 456 7890</p>
                        </div>
                    </div>
                </div>
                
                <div class="card card-custom border-0 shadow-sm p-3 glass-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-3 text-warning bg-warning-subtle rounded p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-envelope-open"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Us</h6>
                            <p class="text-muted small mb-0">support@bibliotech.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inquiry Form -->
        <div class="col-lg-7">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <h3 class="fw-bold mb-4">Send Message</h3>
                    
                    <form action="<?php echo BASE_URL; ?>/index.php?route=contact" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label small fw-medium">Full Name</label>
                                <input type="text" class="form-control form-control-custom" id="name" name="name" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-medium">Email Address</label>
                                <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="john@example.com" required>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label small fw-medium">Your Message</label>
                                <textarea class="form-control form-control-custom" id="message" name="message" rows="5" placeholder="Write details here..." required></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary fw-medium px-4 py-2 w-100" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
