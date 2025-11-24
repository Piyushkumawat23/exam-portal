@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <span><i class="bi bi-shield-lock"></i> Admin Control Panel</span>
        <button onclick="loadAdminForms()" class="btn btn-sm btn-light text-primary fw-bold">Refresh Data</button>
    </div>
    <div class="card-body">
        <div class="row">
            
            <div class="col-md-4 border-end">
                <h5 class="text-success">Create New Exam</h5>
                <hr>
                <form id="createExamForm">
                    <div class="mb-2">
                        <label>Exam Title</label>
                        <input type="text" id="title" class="form-control" placeholder="e.g. Semester 1 Exam" required>
                    </div>
                    <div class="mb-2">
                        <label>Description</label>
                        <textarea id="desc" class="form-control" placeholder="Details..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label>Fees (₹)</label>
                            <input type="number" id="price" class="form-control" required>
                        </div>
                        <div class="col-6 mb-2">
                            <label>Last Date</label>
                            <input type="date" id="date" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-2">Publish Exam</button>
                </form>
            </div>
            
            <div class="col-md-8">
                <h5 class="text-primary">Active Forms & Applicants</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Fees</th>
                                <th>Last Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="adminFormsList">
                            <tr><td colspan="5" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="applicantsModal" tabindex="-1">
    <div class="modal-dialog modal-xl"> <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Applicants & Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Sr.</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Extra Details</th>
                                <th>Status</th>
                                <th>Payment Info (Txn ID)</th> <th>Date Applied</th>
                            </tr>
                        </thead>
                        <tbody id="applicantsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>

    if (!localStorage.getItem('token')) {
        window.location.href = "{{ url('/') }}";
    }

    // Security Check
    fetch(`${BASE_API}/auth/me`, { headers: { 'Authorization': `Bearer ${token}` }})
    .then(res => {
        if (!res.ok) throw new Error('Unauthorized'); // 401 aane par error
        return res.json();
    })
    .then(u => { 
        if(u.role !== 'admin') window.location.href = "{{ url('/student/dashboard') }}"; 
    })
    .catch(() => {
        // Agar API fail ho ya token invalid ho -> Logout
        localStorage.removeItem('token');
        window.location.href = "{{ url('/') }}";
    });
    const headers = { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' };

    // --- LOGIC: LOAD FORMS ---
    async function loadAdminForms() {
        const res = await fetch(`${BASE_API}/forms`, { headers });
        const data = await res.json();
        const tbody = document.getElementById('adminFormsList');
        tbody.innerHTML = '';
        
        if(data.length === 0) tbody.innerHTML = '<tr><td colspan="5" class="text-center">No Forms Created Yet.</td></tr>';

        data.forEach(f => {
            tbody.innerHTML += `
                <tr>
                    <td>${f.id}</td>
                    <td>${f.title}</td>
                    <td>₹${f.price}</td>
                    <td>${f.last_date}</td>
                    <td>
                        <button class="btn btn-sm btn-info text-white mb-1" onclick="viewApplicants(${f.id})">
                            <i class="bi bi-people"></i> View Applicants
                        </button>
                        <button class="btn btn-sm btn-danger mb-1" onclick="deleteForm(${f.id})">Delete</button>
                    </td>
                </tr>`;
        });
    }

    async function viewApplicants(formId) {
        const modal = new bootstrap.Modal(document.getElementById('applicantsModal'));
        modal.show();
        const tbody = document.getElementById('applicantsTableBody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Fetching details...</td></tr>';

        try {
            const res = await fetch(`${BASE_API}/forms/${formId}/applicants`, { headers });
            if(!res.ok) throw new Error("Failed to fetch");

            const submissions = await res.json();
            tbody.innerHTML = '';

            if(submissions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">No one has applied yet.</td></tr>';
                return;
            }

            submissions.forEach((sub, index) => {
                // 1. Parse JSON Data
                let extraDetails = '-';
                try {
                    const parsed = JSON.parse(sub.data);
                    extraDetails = `Father: ${parsed.father_name || ''}<br>Mobile: ${parsed.mobile || ''}`;
                } catch(e) {}

                // 2. Payment Details Logic
                let paymentInfo = '<span class="text-muted small">Pending</span>';
                let statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';

                if (sub.status === 'paid') {
                    statusBadge = '<span class="badge bg-success">PAID</span>';
                    
                    // Check if payment object exists (Controller update required)
                    if(sub.payment) {
                        paymentInfo = `
                            <div class="small">
                                <strong>Txn ID:</strong> ${sub.payment.payment_id}<br>
                                <strong>Amount:</strong> ₹${sub.payment.amount}
                            </div>
                        `;
                    } else {
                        paymentInfo = '<span class="text-danger small">Paid (Record Missing)</span>';
                    }
                }

                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="fw-bold">${sub.user ? sub.user.name : 'Unknown'}</td>
                        <td>${sub.user ? sub.user.email : '-'}</td>
                        <td><small>${extraDetails}</small></td>
                        <td>${statusBadge}</td>
                        <td>${paymentInfo}</td> <td>${new Date(sub.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });

        } catch(err) {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error fetching data.</td></tr>';
        }
    }

    // --- CREATE FORM ---
    document.getElementById('createExamForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            title: document.getElementById('title').value,
            description: document.getElementById('desc').value,
            price: document.getElementById('price').value,
            last_date: document.getElementById('date').value
        };
        const res = await fetch(`${BASE_API}/forms`, { method: 'POST', headers, body: JSON.stringify(payload) });
        if(res.ok) { 
            showAlert('success', 'Exam Created Successfully!'); 
            document.getElementById('createExamForm').reset();
            loadAdminForms(); 
        }
    });

    // --- DELETE FORM ---
    async function deleteForm(id) {
        if(confirm('Are you sure? This will delete all student applications for this exam too!')) {
            await fetch(`${BASE_API}/forms/${id}`, { method: 'DELETE', headers });
            loadAdminForms();
        }
    }

    loadAdminForms();
</script>
@endsection