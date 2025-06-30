<?php
/**
 * Auth API Controller
 * File: app/Controllers/Api/V1/AuthController.php
 */
namespace App\Controllers\Api\V1;

class AuthController extends BaseApiController
{
    protected function requiresAuth(): bool
    {
        // Only me, updateProfile, and logout require auth
        return in_array($this->request->getMethod(), ['me', 'updateProfile', 'logout']);
    }

    public function login()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $credentials = $this->request->getJSON();
        $auth = auth('session');

        // Attempt login
        $user = $auth->attempt([
            'email' => $credentials->email,
            'password' => $credentials->password
        ]);

        if (!$user) {
            return $this->failUnauthorized('Invalid credentials');
        }

        // Generate API token
        $apiKeyModel = model('App\Models\CMS\ApiKeyModel');
        $apiKey = $apiKeyModel->generateKey($user->id, 'API Login Token', [
            'permissions' => ['api.access'],
            'rate_limit' => 1000,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ]);

        return $this->respond([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ],
                'token' => [
                    'key' => $apiKey->key,
                    'secret' => $apiKey->plain_secret,
                    'expires_at' => $apiKey->expires_at
                ]
            ]
        ]);
    }

    public function register()
    {
        $rules = [
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $users = auth()->getProvider();

        $user = new \CodeIgniter\Shield\Entities\User($data);
        $users->save($user);

        if ($users->errors()) {
            return $this->fail($users->errors());
        }

        // Add to user group
        $userId = $users->getInsertID();
        $db = \Config\Database::connect();
        $db->table('auth_groups_users')->insert([
            'user_id' => $userId,
            'group' => 'user',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respondCreated([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'id' => $userId,
                'username' => $data['username'],
                'email' => $data['email']
            ]
        ]);
    }

    public function me()
    {
        $user = auth()->user();

        return $this->respond([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'groups' => $this->getUserGroups($user->id),
                'permissions' => $this->getUserPermissions($user->id)
            ]
        ]);
    }

    private function getUserGroups(int $userId): array
    {
        $db = \Config\Database::connect();
        return $db->table('auth_groups_users')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
    }

    private function getUserPermissions(int $userId): array
    {
        $db = \Config\Database::connect();
        return $db->table('auth_permissions_users')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
    }
}