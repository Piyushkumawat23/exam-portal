@extends('layouts.app')

@section('title', 'Login / Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4>Portal Access</h4>
            </div>
            <div class="card-body">
                
                <!-- TABS -->
                <ul class="nav nav-tabs mb-3" id="authTabs">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#login">Login</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#register">Register</button></li>
                </ul>

                <div class="tab-content">
                    <!-- LOGIN FORM -->
                    <div class="tab-pane fade show active" id="login">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" id="l_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" id="l_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>

                    <!-- REGISTER FORM -->
                    <div class="tab-pane fade" id="register">
                        <form id="registerForm">
                            <div class="mb-3">
                                <label>Full Name</label>
                                <input type="text" id="r_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" id="r_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" id="r_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Create Account</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    if(localStorage.getItem('token')) {
        window.location.href = "{{ url('/dashboard') }}";
    }

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault(); 
    console.log("Login button clicked...");

    try {
        const res = await fetch(`${BASE_API}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: document.getElementById('l_email').value,
                password: document.getElementById('l_password').value
            })
        });

        const data = await res.json();
        console.log("Response:", data); 

        if (res.ok) {
            // Token Save Karein
            localStorage.setItem('token', data.token);
            console.log("Token saved:", localStorage.getItem('token'));
            
            // alert("Login Successful!");
            setTimeout(() => {
                window.location.href = "{{ url('/dashboard') }}";
            }, 500); 
        } else {
            showAlert('danger', data.error || 'Login Failed');
        }
    } catch (err) {
        console.error("Login Error:", err);
        showAlert('danger', 'Server Error');
    }
});

    // REGISTER
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const res = await fetch(`${BASE_API}/auth/register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: document.getElementById('r_name').value,
                    email: document.getElementById('r_email').value,
                    password: document.getElementById('r_password').value
                })
            });
            if(res.ok) {
                showAlert('success', 'Registered! Please Login.');
                document.querySelector('#authTabs button[data-bs-target="#login"]').click();
            } else {
                showAlert('danger', 'Registration Failed');
            }
        } catch(err) { showAlert('danger', 'Server Error'); }
    });
</script>
@endsection