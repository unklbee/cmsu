<?php

namespace App\Controllers\Admin;

use App\Controllers\CMS\BaseAdminController;
use App\Controllers\CMS\Traits\CrudTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

/**
 * Users Controller
 * File: app/Controllers/Admin/Users.php
 */
class Users extends BaseAdminController
{
    use CrudTrait;

    protected $model;
    protected array $validation = [
        'username' => 'required|string|min_length[3]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email',
        'active' => 'required|in_list[0,1]'
    ];

    public function __construct()
    {
        $this->model = auth()->getProvider();
    }

    /**
     * Override index from CrudTrait to add custom data
     */
    public function index(): string
    {
        $this->checkPermission('admin.users');
        $this->setTitle('Users');

        $data = [
            'users' => $this->model->findAll(),
            'groups' => $this->getGroups()
        ];

        return $this->render('users/index', $data);
    }

    /**
     * Override create to add groups data
     */
    public function create(): string
    {
        $this->checkPermission('admin.users');
        $this->setTitle('Create User');

        $data = [
            'groups' => $this->getGroups()
        ];

        return $this->render('users/create', $data);
    }

    /**
     * Override store to handle groups
     */
    public function store()
    {
        $this->checkPermission('admin.users');

        // Add password validation for new user
        $this->validation['password'] = 'required|min_length[8]';

        if (!$this->validate($this->validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $groups = $data['groups'] ?? [];
        unset($data['groups']);

        // Create user using Shield
        $user = new \CodeIgniter\Shield\Entities\User($data);
        $this->model->save($user);

        if ($this->model->errors()) {
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . implode(', ', $this->model->errors()));
        }

        $userId = $this->model->getInsertID();

        // Assign groups
        $this->assignGroups($userId, $groups);

        return redirect()->to('/admin/users')->with('success', 'User created successfully');
    }

    /**
     * Override edit to add user data and groups
     */
    public function edit($id): string
    {
        $this->checkPermission('admin.users');
        $this->setTitle('Edit User');

        $user = $this->model->findById($id);

        if (!$user) {
            throw new PageNotFoundException();
        }

        $data = [
            'user' => $user,
            'groups' => $this->getGroups(),
            'userGroups' => $this->getUserGroups($id)
        ];

        return $this->render('users/edit', $data);
    }

    /**
     * Override update to handle groups and optional password
     */
    public function update($id)
    {
        $this->checkPermission('admin.users');

        // Make password optional for updates
        if ($this->request->getPost('password')) {
            $this->validation['password'] = 'min_length[8]';
        }

        if (!$this->validate($this->validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $groups = $data['groups'] ?? [];
        unset($data['groups']);

        // If password is empty, don't update it
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Update user
        $user = $this->model->findById($id);
        if (!$user) {
            throw new PageNotFoundException();
        }

        $user->fill($data);
        $this->model->save($user);

        if ($this->model->errors()) {
            return redirect()->back()->withInput()->with('error', 'Failed to update user: ' . implode(', ', $this->model->errors()));
        }

        // Update groups
        $this->assignGroups($id, $groups);

        return redirect()->to('/admin/users')->with('success', 'User updated successfully');
    }

    /**
     * Override delete to prevent self-deletion
     */
    public function delete($id): ResponseInterface|RedirectResponse
    {
        $this->checkPermission('admin.users');

        if ($id == auth()->id()) {
            if ($this->request->isAJAX()) {
                return $this->error('Cannot delete your own account');
            }
            return redirect()->back()->with('error', 'Cannot delete your own account');
        }

        // Use parent's delete method
        return parent::delete($id);
    }

    /**
     * Helper: Get available groups
     */
    private function getGroups(): array
    {
        return [
            'superadmin' => 'Super Administrator',
            'admin' => 'Administrator',
            'editor' => 'Editor',
            'user' => 'User'
        ];
    }

    /**
     * Helper: Get user's current groups
     */
    private function getUserGroups(int $userId): array
    {
        $db = Database::connect();
        $groups = $db->table('auth_groups_users')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_column($groups, 'group');
    }

    /**
     * Helper: Assign groups to user
     */
    private function assignGroups(int $userId, array $groups): void
    {
        $db = Database::connect();

        // Remove existing groups
        $db->table('auth_groups_users')->where('user_id', $userId)->delete();

        // Add new groups
        foreach ($groups as $group) {
            $db->table('auth_groups_users')->insert([
                'user_id' => $userId,
                'group' => $group,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Override getRedirectUrl for custom admin path
     */
    protected function getRedirectUrl(string $action): string
    {
        return site_url('admin/users');
    }
}

