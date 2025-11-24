@extends('layouts.app')
@section('title', 'Redirecting...')

@section('content')
<div class="text-center mt-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2">Checking your role & redirecting...</p>
</div>
@endsection

@section('scripts')
<script>
    if(!token) window.location.href = "{{ url('/') }}";

    // API se Role Check Karo
    fetch(`${BASE_API}/auth/me`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(user => {
        if(user.role === 'admin') {
            window.location.href = "{{ url('/admin/dashboard') }}";
        } else {
            window.location.href = "{{ url('/student/dashboard') }}";
        }
    })
    .catch(() => {
        localStorage.removeItem('token');
        window.location.href = "{{ url('/') }}";
    });
</script>
@endsection