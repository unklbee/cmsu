<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\LoginController as ShieldLogin;

/**
 * Custom Auth Controller
 * File: app/Controllers/Auth.php
 */
class Auth extends ShieldLogin
{
    /**
     * Attempts to log the user in.
     */
    public function loginAction(): \CodeIgniter\HTTP\RedirectResponse
    {
        // Validate here first
        $rules = $this->getValidationRules();

        if (! $this->validateData($this->request->getPost(), $rules, [], config('Auth')->DBGroup)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $credentials             = $this->request->getPost(setting('Auth.validFields'));
        $credentials             = array_filter($credentials);
        $credentials['password'] = $this->request->getPost('password');
        $remember                = (bool) $this->request->getPost('remember');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // Attempt to login
        $result = $authenticator->remember($remember)->attempt($credentials);
        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        // If an action has been defined for login, start it up.
        if ($authenticator->hasAction()) {
            return redirect()->route('auth-action-show');
        }

        // Debug: Log the redirect
        log_message('info', 'User logged in: ' . auth()->user()->username);
        log_message('info', 'Has admin access: ' . (has_permission('admin.access') ? 'YES' : 'NO'));

        // Custom redirect based on user role
        return $this->redirectAfterLogin();
    }

    /**
     * Custom redirect after login based on user role
     */
    protected function redirectAfterLogin()
    {
        $user = auth()->user();

        // Debug
        log_message('info', 'Checking redirect for user: ' . $user->username);

        // Check if user has admin access
        if (has_permission('admin.access')) {
            log_message('info', 'Redirecting to admin dashboard');
            // Force absolute redirect
            return redirect()->to(site_url('admin'))->with('success', 'Welcome back, ' . $user->username . '!');
        }

        // Check if there's a redirect URL in session
        if (session()->has('redirect_url')) {
            $redirectUrl = session('redirect_url');
            session()->remove('redirect_url');
            return redirect()->to($redirectUrl);
        }

        // Default redirect to home with welcome message
        log_message('info', 'Redirecting to home page');
        return redirect()->to(site_url('/'))->with('success', 'Welcome back, ' . $user->username . '!');
    }

    /**
     * Logout action
     */
    public function logoutAction(): \CodeIgniter\HTTP\RedirectResponse
    {
        // Logout the user
        auth()->logout();

        return redirect()->to('/')->with('success', 'You have been logged out successfully.');
    }
}
