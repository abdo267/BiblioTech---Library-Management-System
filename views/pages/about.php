<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}
?>

<div class="container py-5 fade-in-up">
  
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">About Us</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="text-center mb-5">
        <h1 class="fw-extrabold display-5">About BiblioTech Library</h1>
        <p class="text-muted col-lg-8 mx-auto lead">Connecting people with ideas, stories, and knowledge since 2026. We are dedicated to providing resource environments for learners, researchers, and book lovers.</p>
    </div>

    <!-- Core Pillars Grid -->
    <div class="row g-4 align-items-center mb-5 py-3">
        <div class="col-lg-6">
            <h2 class="fw-bold mb-3">Our Core Mission</h2>
            <p>At BiblioTech, our mission is to stimulate curiosity, inspire learning, and foster library community connections. We strive to provide easy, automated access to educational materials, novels, reference data, and IT tools.</p>
            <p class="text-muted">Through continuous optimization, we link offline catalog shelves with convenient online systems. Search for books, check real-time available quantities, request rentals, and manage borrow limits dynamically.</p>
            
            <div class="row g-3 mt-3">
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check text-primary"></i>
                        <span class="fw-medium">24/7 Catalog Search</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check text-primary"></i>
                        <span class="fw-medium">Automated Borrowings</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check text-primary"></i>
                        <span class="fw-medium">Overdue Reminders</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check text-primary"></i>
                        <span class="fw-medium">Wide Classifications</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg glass-card overflow-hidden">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Library Borrow Rules</h3>
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="d-flex align-items-start gap-3">
                            <span class="badge bg-primary rounded-circle p-2 mt-1" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                            <div>
                                <h6 class="fw-semibold mb-0">Request Approval</h6>
                                <p class="small text-muted mb-0">Submit requests online. Wait for librarians to approve before taking books.</p>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <span class="badge bg-primary rounded-circle p-2 mt-1" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                            <div>
                                <h6 class="fw-semibold mb-0">14-Day Lending Window</h6>
                                <p class="small text-muted mb-0">Books are loaned for a default duration of 2 weeks. Extensions must be processed by the admin.</p>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <span class="badge bg-primary rounded-circle p-2 mt-1" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                            <div>
                                <h6 class="fw-semibold mb-0">Overdue Policies</h6>
                                <p class="small text-muted mb-0">Overdue books incur a fine of 100 EGP per day past the due date. Accounts with unpaid fines will be temporarily locked.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
