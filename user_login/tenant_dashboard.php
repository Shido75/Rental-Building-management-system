<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - Rental House Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Rental House Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['tenant_name']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Personal Information Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-user-circle"></i> Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong><i class="fas fa-user"></i> Name:</strong> 
                                <?php echo htmlspecialchars($_SESSION['tenant_name']); ?>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-envelope"></i> Email:</strong> 
                                <?php echo htmlspecialchars($_SESSION['tenant_email']); ?>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-phone"></i> Phone:</strong> 
                                <?php echo htmlspecialchars($_SESSION['tenant_phone']); ?>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-briefcase"></i> Profession:</strong> 
                                <?php echo htmlspecialchars($_SESSION['tenant_profession']); ?>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-calendar-alt"></i> Admission Date:</strong> 
                                <?php echo htmlspecialchars($_SESSION['tenant_admission_date']); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- House Information Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-home"></i> House Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong><i class="fas fa-building"></i> House Name:</strong> 
                                <?php echo htmlspecialchars($_SESSION['tenant_house_name']); ?>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-money-bill-wave"></i> Rent Amount:</strong> 
                                KES <?php echo number_format($_SESSION['tenant_rent'], 2); ?>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-wallet"></i> Account Balance:</strong> 
                                <span class="<?php echo $_SESSION['tenant_balance'] >= 0 ? 'text-success' : 'text-danger'; ?>" data-balance>
                                    KES <?php echo number_format($_SESSION['tenant_balance'], 2); ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-tasks"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" id="viewBillsBtn">
                                <i class="fas fa-file-invoice"></i> View Bills
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-money-bill"></i> Make Payment
                            </button>
                            <button class="btn btn-warning" id="reportIssueBtn">
                                <i class="fas fa-exclamation-circle"></i> Report Issue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bills Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar"></i> Bills
                            <button class="btn btn-sm btn-primary float-end" id="refreshBills">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Bill Type</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody id="billsTableBody">
                                    <!-- Bills will be loaded here dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <div id="noBillsMessage" class="text-center p-3 d-none">
                            <p class="text-muted">No bills found.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill"></i> Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (KES)</label>
                            <input type="number" class="form-control" id="amount" name="amount" required min="1" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-control" id="paymentMethod" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="M-PESA">M-PESA</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transactionId" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="transactionId" name="transaction_id" required>
                            <small class="form-text text-muted">For M-PESA, enter the M-PESA confirmation code</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="submitPayment">Submit Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Payment form handling
            const submitPayment = document.getElementById('submitPayment');
            const paymentForm = document.getElementById('paymentForm');
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));

            submitPayment.addEventListener('click', async function() {
                if (!paymentForm.checkValidity()) {
                    paymentForm.reportValidity();
                    return;
                }

                const formData = {
                    amount: document.getElementById('amount').value,
                    payment_method: document.getElementById('paymentMethod').value,
                    transaction_id: document.getElementById('transactionId').value
                };

                try {
                    const response = await fetch('/Rental-house-management-system/user_login/api/make_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Payment successful! New balance: KES ' + data.data.new_balance);
                        paymentModal.hide();
                        // Update the balance display without refreshing
                        document.querySelector('[data-balance]').textContent = 
                            'KES ' + parseFloat(data.data.new_balance).toLocaleString(undefined, {minimumFractionDigits: 2});
                        // Reset form
                        paymentForm.reset();
                    } else {
                        alert(data.message || 'Payment failed. Please try again.');
                    }
                } catch (error) {
                    console.error('Payment error:', error);
                    alert('An error occurred. Please try again later.');
                }
            });

            // Bills functionality
            const billsTableBody = document.getElementById('billsTableBody');
            const noBillsMessage = document.getElementById('noBillsMessage');
            const refreshBillsBtn = document.getElementById('refreshBills');

            async function loadBills() {
                try {
                    const response = await fetch('/Rental-house-management-system/user_login/api/get_bills.php');
                    const data = await response.json();

                    if (data.success) {
                        const bills = data.data.bills;
                        billsTableBody.innerHTML = '';

                        if (bills.length === 0) {
                            noBillsMessage.classList.remove('d-none');
                            return;
                        }

                        noBillsMessage.classList.add('d-none');
                        bills.forEach(bill => {
                            const row = document.createElement('tr');
                            const statusClass = {
                                'Pending': 'text-warning',
                                'Paid': 'text-success',
                                'Overdue': 'text-danger'
                            }[bill.status] || '';

                            row.innerHTML = `
                                <td>${bill.bill_type}</td>
                                <td>KES ${parseFloat(bill.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                <td>${new Date(bill.due_date).toLocaleDateString()}</td>
                                <td><span class="${statusClass}">${bill.status}</span></td>
                                <td>${new Date(bill.created_at).toLocaleString()}</td>
                            `;
                            billsTableBody.appendChild(row);
                        });
                    } else {
                        throw new Error(data.message || 'Failed to load bills');
                    }
                } catch (error) {
                    console.error('Error loading bills:', error);
                    alert('Failed to load bills. Please try again later.');
                }
            }

            // Load bills on page load
            loadBills();

            // Refresh bills when refresh button is clicked
            refreshBillsBtn.addEventListener('click', loadBills);
        });
    </script>
</body>
</html> 