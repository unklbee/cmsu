<?php

/**
 * Frontend Auth Pages Controller
 * File: app/Controllers/AuthPages.php
 */
namespace App\Controllers;

/**
 * Custom Login/Register Pages with better UI
 */
class AuthPages extends BaseController
{
    public function login(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/admin');
        }

        return view('auth/login');
    }

    public function register(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/admin');
        }

        return view('auth/register');
    }

    public function forgotPassword(): string
    {
        return view('auth/forgot_password');
    }
}