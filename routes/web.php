<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('login');

// 1. Main Dashboard
Route::get('/dashboard', function () {
    return view('dashboard-router');
});

// 2. Admin Dashboard View
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

// 3. Student Dashboard View
Route::get('/student/dashboard', function () {
    return view('student.dashboard');
});

// 3. Admin: Manage Forms (Edit/Delete)
Route::get('/admin/forms', function () {
    return view('admin-forms');
});

// 4. View Submission & Payment
Route::get('/submission/{id}', function ($id) {
    return view('submission-view', ['submission_id' => $id]);
});


