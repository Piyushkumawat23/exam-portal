@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs mb-3" id="studentTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-exams">Available Exams</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#my-apps" onclick="loadMySubmissions()">My Applications</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-exams">
                <div class="row" id="examsContainer">
                    <p class="text-center">Loading Exams...</p>
                </div>
            </div>

            <div class="tab-pane fade" id="my-apps">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">My Application Status</h5>
                        <table class="table table-hover mt-3">
                            <thead class="table-light">
                                <tr>
                                    <th>App ID</th>
                                    <th>Exam Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="mySubmissionsList"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Fill Application Details</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="submissionForm">
                    <input type="hidden" id="modalFormId">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" id="s_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Father's Name</label>
                        <input type="text" id="s_father" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mobile Number</label>
                        <input type="number" id="s_mobile" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Secure Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closePayModal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h4>Total Amount: <span id="payAmount" class="fw-bold">₹0</span></h4>
                    <p class="text-muted">Enter your card details below</p>
                </div>
                
                <div id="card-element" class="form-control p-3 mb-3">
                    </div>
                
                <div id="card-errors" class="text-danger small mb-3" role="alert"></div>

                <button id="confirmPayBtn" class="btn btn-success w-100 py-2">Pay Now</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>

<script>

    if (!localStorage.getItem('token')) {
        window.location.href = "{{ url('/') }}";
    }
    
    // Security Check
    fetch(`${BASE_API}/auth/me`, { headers: { 'Authorization': `Bearer ${token}` }})
    .then(res => res.json())
    .then(u => { if(u.role === 'admin') window.location.href = "{{ url('/admin/dashboard') }}"; });

    const headers = { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' };

    // --- STRIPE SETUP ---
    const stripe = Stripe("pk_test_51SWI8CImWk8oaKXxj3GoTcfXTg1dBFBkV05mxymjKxvkDxH2f6osDYnbvOnGsTItEO1AKRYBujh4rUTyPjNHfaWn00Uun1acce"); 
    const elements = stripe.elements();
    
    // Card Style Customization
    const style = {
        base: { fontSize: '16px', color: '#32325d', fontFamily: 'Arial, sans-serif' }
    };
    const card = elements.create('card', { style: style });
    card.mount('#card-element'); 

    // Handle Validation Errors
    card.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    let currentSubmissionId = null;

    // --- 1. LOAD AVAILABLE EXAMS ---
    async function loadExams() {
        const res = await fetch(`${BASE_API}/forms`, { headers });
        const data = await res.json();
        const div = document.getElementById('examsContainer');
        div.innerHTML = '';
        
        if(data.length === 0) div.innerHTML = '<div class="alert alert-info">No exams active currently.</div>';

        data.forEach(f => {
            div.innerHTML += `
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title text-primary">${f.title}</h5>
                            <p class="small text-muted">${f.description}</p>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Fee: ₹${f.price}</span>
                                <span class="text-danger">Ends: ${f.last_date}</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <button onclick="openApply(${f.id})" class="btn btn-outline-primary w-100">Apply Now</button>
                        </div>
                    </div>
                </div>`;
        });
    }

    // --- 2. APPLY LOGIC ---
    function openApply(id) {
        document.getElementById('modalFormId').value = id;
        new bootstrap.Modal(document.getElementById('applyModal')).show();
    }

    document.getElementById('submissionForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            full_name: document.getElementById('s_name').value,
            father_name: document.getElementById('s_father').value,
            mobile: document.getElementById('s_mobile').value
        };
        
        const res = await fetch(`${BASE_API}/submissions`, {
            method: 'POST', headers,
            body: JSON.stringify({
                form_id: document.getElementById('modalFormId').value,
                data: JSON.stringify(formData)
            })
        });

        if(res.ok) {
            showAlert('success', 'Application Submitted!');
            bootstrap.Modal.getInstance(document.getElementById('applyModal')).hide();
            document.getElementById('submissionForm').reset();
            const triggerEl = document.querySelector('#studentTabs button[data-bs-target="#my-apps"]');
            bootstrap.Tab.getInstance(triggerEl).show();
            loadMySubmissions();
        }
    });

    // --- 3. MY SUBMISSIONS ---
    async function loadMySubmissions() {
        const tbody = document.getElementById('mySubmissionsList');
        tbody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
        
        const res = await fetch(`${BASE_API}/user/submissions`, { headers });
        const subs = await res.json();
        tbody.innerHTML = '';

        if(subs.length === 0) tbody.innerHTML = '<tr><td colspan="4">No applications found.</td></tr>';

        subs.forEach(s => {
            const examName = s.form ? `<strong class="text-primary">${s.form.title}</strong>` : `ID: ${s.form_id}`;
            const fee = s.form ? s.form.price : 0;
            
            let actionBtn = '';

            if(s.status === 'submitted') {
                // Pass fee to payment function
                actionBtn = `<button onclick="openPaymentModal(${s.id}, ${fee})" class="btn btn-sm btn-warning">Pay Now</button>`;
            } else if(s.status === 'paid') {
                const payId = s.payment ? s.payment.id : null;
                if(payId) {
                    actionBtn = `<button onclick="downloadReceipt(${payId})" class="btn btn-sm btn-success">Download PDF</button>`;
                } else {
                    actionBtn = `<span class="text-success fw-bold">Paid</span>`;
                }
            }

            tbody.innerHTML += `
                <tr>
                    <td>#${s.id}</td>
                    <td>${examName}</td>
                    <td><span class="badge bg-${s.status=='paid'?'success':'secondary'}">${s.status.toUpperCase()}</span></td>
                    <td>${actionBtn}</td>
                </tr>`;
        });
    }

    // --- 4. PAYMENT LOGIC (STRIPE) ---
    
    // Step A: Open Modal & Show Amount
    function openPaymentModal(subId, amount) {
        currentSubmissionId = subId;
        document.getElementById('payAmount').innerText = "₹" + amount;
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }

    // Step B: Confirm Payment Click
// Step B: Confirm Payment Click
document.getElementById('confirmPayBtn').addEventListener('click', async function() {
    const btn = document.getElementById('confirmPayBtn');
    btn.disabled = true;
    btn.innerText = "Processing...";

    try {
        // 1. Backend se Client Secret Mangwao
        const intentRes = await fetch(`${BASE_API}/payments/create-intent`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ submission_id: currentSubmissionId })
        });

        const intentData = await intentRes.json();

        if(!intentRes.ok) {
            throw new Error(intentData.error || "Failed to create payment intent");
        }

        const clientSecret = intentData.clientSecret;

        // 2. Stripe se Card Confirm karo
        const result = await stripe.confirmCardPayment(clientSecret, {
            payment_method: { card: card }
        });

        if (result.error) {
            // --- CHANGE START: PAYMENT FAILURE HANDLING ---
            document.getElementById('card-errors').textContent = result.error.message;
            btn.disabled = false;
            btn.innerText = "Pay Now";

            // Backend ko batao ki payment fail ho gaya
            fetch(`${BASE_API}/payments/fail`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ submission_id: currentSubmissionId })
            }).catch(console.error);
            // --- CHANGE END ---
            
        } else {
            if (result.paymentIntent.status === 'succeeded') {
                await confirmBackendPayment(result.paymentIntent.id, currentSubmissionId);
            }
        }
    } catch(err) {
        console.error(err);
        document.getElementById('card-errors').textContent = err.message;
        btn.disabled = false;
        btn.innerText = "Pay Now";
    }
});

    // Step C: Update Database after success
    async function confirmBackendPayment(paymentId, subId) {
        const res = await fetch(`${BASE_API}/payments/confirm`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                submission_id: subId,
                payment_id: paymentId,
                method: 'stripe'
            })
        });

        if(res.ok) {
            showAlert('success', 'Payment Successful! Receipt Generated.');
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            // Reset Button
            document.getElementById('confirmPayBtn').disabled = false;
            document.getElementById('confirmPayBtn').innerText = "Pay Now";
            // Refresh List
            loadMySubmissions();
        } else {
            showAlert('danger', 'Payment Verified but DB Update Failed.');
        }
    }

    function downloadReceipt(payId) {
        window.location.href = `${BASE_API}/payments/receipt/${payId}?token=${token}`;
    }

    loadExams();
</script>
@endsection