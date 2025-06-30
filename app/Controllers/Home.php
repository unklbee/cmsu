<?php

namespace App\Controllers;

/**
 * Home Controller - Frontend
 * File: app/Controllers/Home.php
 */
class Home extends BaseController
{
    protected $cms;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Initialize CMS
        $this->cms = service('cms');
        $this->cms->initialize();
    }

    public function index()
    {
        $data = [
            'title' => cms_setting('site_name'),
            'description' => cms_setting('site_description')
        ];

        // If blog module is active, get recent posts
        if (module_enabled('blog')) {
            $postModel = model('App\Modules\Blog\Models\PostModel');
            $data['recent_posts'] = $postModel->getPublished(6);
        }

        return view('home', $data);
    }

    public function about()
    {
        $data = [
            'title' => 'About Us',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => site_url()],
                ['title' => 'About Us']
            ]
        ];

        return view('about', $data);
    }

    public function contact()
    {
        $data = [
            'title' => 'Contact Us',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => site_url()],
                ['title' => 'Contact Us']
            ]
        ];

        return view('contact', $data);
    }

    public function submitContact()
    {
        $rules = [
            'name' => 'required|string|max_length[100]',
            'email' => 'required|valid_email',
            'subject' => 'required|string|max_length[200]',
            'message' => 'required|string|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();

        // Send email notification
        $email = \Config\Services::email();
        $email->setTo(setting('site_email'));
        $email->setFrom($data['email'], $data['name']);
        $email->setSubject('Contact Form: ' . $data['subject']);
        $email->setMessage(view('emails/contact', $data));

        if ($email->send()) {
            return redirect()->to('/contact')->with('success', 'Thank you for your message. We will get back to you soon!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to send message. Please try again.');
    }
}
