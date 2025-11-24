<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Portal - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">Exam Portal System</a>
            
            <div class="d-none text-white align-items-center" id="navAuthSection">
                <span class="me-3 small">
                    Hello, <b id="navUser">User</b> 
                    <span class="badge bg-light text-dark ms-1" id="navRole">Guest</span>
                </span>
                <button onclick="logout()" class="btn btn-sm btn-danger">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div id="alertBox" class="alert d-none"></div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // GLOBAL CONFIGURATION
        const BASE_API = "{{ url('/api') }}";
        const token = localStorage.getItem('token');

        // Check Login Status for Navbar
        if(token) {
            const navAuth = document.getElementById('navAuthSection');
            
            // CHANGE 2: Toggle Classes to Show
            navAuth.classList.remove('d-none'); // Hide 'hidden' class
            navAuth.classList.add('d-flex');    // Add 'flex' class to align items

            // Fetch User Details briefly for Navbar
            fetch(`${BASE_API}/auth/me`, {
                headers: { 'Authorization': `Bearer ${token}` }
            })
            .then(res => res.json())
            .then(user => {
                if(user.name) {
                    document.getElementById('navUser').innerText = user.name;
                    document.getElementById('navRole').innerText = user.role.toUpperCase();
                }
            })
            .catch(() => logout()); // Token invalid? Logout.
        }

        // Global Logout Function
        function logout() {
            localStorage.removeItem('token');
            window.location.href = "{{ url('/') }}";
        }

        // Global Alert Function
        function showAlert(type, msg) {
            const box = document.getElementById('alertBox');
            if(box) {
                box.className = `alert alert-${type}`;
                box.innerText = msg;
                box.classList.remove('d-none');
                setTimeout(() => box.classList.add('d-none'), 3000);
            } else {
                alert(msg);
            }
        }
    </script>

    @yield('scripts')

</body>
</html>