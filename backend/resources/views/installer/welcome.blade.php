@extends('installer.layout')

@section('content')
    <div class="welcome-container">

        <!-- Logo -->
        <div class="logo-box">
            <img src="{{ asset('assets/logo.png') }}" alt="Comanda Logo" class="logo">
        </div>

        <!-- Headline -->
        <h1 class="welcome-title">Welcome to Comanda Restaurant POS 👋</h1>
        <p class="welcome-subtitle">
            Comanda is a <strong>modern restaurant management & POS system</strong>
            that simplifies operations, improves efficiency, and empowers your business.
        </p>

        <!-- Feature Badges -->
        <div class="badges">
            <span>🍽 Dine-in & Takeaway</span>
            <span>💳 POS Register</span>
            <span>📦 Inventory Control</span>
            <span>📊 Sales Reports</span>
        </div>
        
        <a href="{{ route('installer.requirements') }}" class="btn-primary"> Start Installation</a>
    </div>
@endsection
