<?php

/**
 * CRUD Trait for controllers
 */
namespace App\Controllers\CMS\Traits;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

trait CrudTrait
{
    protected $model;
//    protected $validation;

    /**
     * Display listing
     */
    public function index()
    {
        $data = $this->model->findAll();

        if ($this->request->isAJAX()) {
            return $this->success($data);
        }

        return $this->render('index', [
            'data' => $data
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return $this->render('create');
    }

    /**
     * Store new record
     */
    public function store()
    {
        $data = $this->request->getPost();

        if (!$this->validate($this->validation)) {
            if ($this->request->isAJAX()) {
                return $this->error('Validation failed', $this->validator->getErrors());
            }

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = $this->model->insert($data);

        if ($id) {
            if ($this->request->isAJAX()) {
                return $this->success(['id' => $id], 'Created successfully');
            }

            return redirect()->to($this->getRedirectUrl('index'))
                ->with('success', 'Created successfully');
        }

        if ($this->request->isAJAX()) {
            return $this->error('Failed to create');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        return $this->render('edit', [
            'data' => $data
        ]);
    }

    /**
     * Update record
     * @throws ReflectionException
     */
    public function update($id)
    {
        $data = $this->request->getPost();

        if (!$this->validate($this->validation)) {
            if ($this->request->isAJAX()) {
                return $this->error('Validation failed', $this->validator->getErrors());
            }

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->model->update($id, $data)) {
            if ($this->request->isAJAX()) {
                return $this->success(null, 'Updated successfully');
            }

            return redirect()->to($this->getRedirectUrl('index'))
                ->with('success', 'Updated successfully');
        }

        if ($this->request->isAJAX()) {
            return $this->error('Failed to update');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update');
    }

    /**
     * Delete record
     */
    public function delete($id): ResponseInterface|RedirectResponse
    {
        if ($this->model->delete($id)) {
            if ($this->request->isAJAX()) {
                return $this->success(null, 'Deleted successfully');
            }

            return redirect()->to($this->getRedirectUrl('index'))
                ->with('success', 'Deleted successfully');
        }

        if ($this->request->isAJAX()) {
            return $this->error('Failed to delete');
        }

        return redirect()->back()->with('error', 'Failed to delete');
    }

    /**
     * Get redirect URL
     */
    protected function getRedirectUrl(string $action): string
    {
        $class = get_class($this);
        $controller = strtolower(str_replace('Controller', '', class_basename($class)));

        return site_url('admin/' . $controller . '/' . $action);
    }
}