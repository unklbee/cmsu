<?php

namespace App\Models\CMS;

use App\Entities\CMS\MenuEntity;

class MenuModel extends BaseModel
{
    protected $table = 'cms_menus';
    protected $primaryKey = 'id';
    protected $returnType = MenuEntity::class;
    protected $allowedFields = [
        'parent_id', 'menu_group', 'title', 'url',
        'route_name', 'icon', 'target', 'permission',
        'order', 'is_active', 'metadata'
    ];

    protected $validationRules = [
        'title' => 'required|string',
        'menu_group' => 'required|string',
        'target' => 'in_list[_self,_blank]'
    ];

    protected array $casts = [
        'metadata' => 'json',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    // Cache for menu trees
    private static $menuCache = [];

    /**
     * Get menu tree by group
     */
    public function getMenuTree(string $group = 'main', bool $activeOnly = true): array
    {
        $cacheKey = $group . '_' . ($activeOnly ? '1' : '0');

        if (isset(self::$menuCache[$cacheKey])) {
            return self::$menuCache[$cacheKey];
        }

        $query = $this->where('menu_group', $group);

        if ($activeOnly) {
            $query->where('is_active', 1);
        }

        $menus = $query->orderBy('parent_id', 'ASC')
            ->orderBy('order', 'ASC')
            ->findAll();

        $tree = $this->buildTree($menus);
        self::$menuCache[$cacheKey] = $tree;

        return $tree;
    }

    /**
     * Build hierarchical tree from flat array
     */
    private function buildTree(array $menus, $parentId = null): array
    {
        $tree = [];

        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $children = $this->buildTree($menus, $menu->id);

                if ($children) {
                    $menu->children = $children;
                }

                // Check permission
                if ($menu->permission && !$this->hasPermission($menu->permission)) {
                    continue;
                }

                $tree[] = $menu;
            }
        }

        return $tree;
    }

    /**
     * Get menu groups
     */
    public function getGroups(): array
    {
        return $this->distinct()
            ->select('menu_group')
            ->orderBy('menu_group', 'ASC')
            ->findAll();
    }

    /**
     * Create menu item
     */
    public function createMenuItem(array $data): bool|int
    {
        // Auto set order if not provided
        if (!isset($data['order'])) {
            $data['order'] = $this->getNextOrder($data['menu_group'], $data['parent_id'] ?? null);
        }

        // Clear cache
        self::$menuCache = [];

        return $this->insert($data);
    }

    /**
     * Update menu item
     */
    public function updateMenuItem(int $id, array $data): bool
    {
        // Clear cache
        self::$menuCache = [];

        return $this->update($id, $data);
    }

    /**
     * Delete menu item (and children)
     */
    public function deleteMenuItem(int $id, bool $deleteChildren = true): bool
    {
        if ($deleteChildren) {
            // Delete all children recursively
            $children = $this->where('parent_id', $id)->findAll();

            foreach ($children as $child) {
                $this->deleteMenuItem($child->id, true);
            }
        } else {
            // Move children to parent level
            $menu = $this->find($id);
            if ($menu) {
                $this->where('parent_id', $id)
                    ->set(['parent_id' => $menu->parent_id])
                    ->update();
            }
        }

        // Clear cache
        self::$menuCache = [];

        return $this->delete($id);
    }

    /**
     * Reorder menu items
     */
    public function reorder(array $items): bool
    {
        $success = true;

        foreach ($items as $order => $id) {
            if (!$this->update($id, ['order' => $order])) {
                $success = false;
            }
        }

        // Clear cache
        self::$menuCache = [];

        return $success;
    }

    /**
     * Move menu item
     */
    public function moveItem(int $id, ?int $newParentId, string $position = 'last'): bool
    {
        $menu = $this->find($id);

        if (!$menu) {
            return false;
        }

        // Check for circular reference
        if ($newParentId && $this->isDescendant($id, $newParentId)) {
            throw new \Exception("Cannot move parent to its own descendant");
        }

        // Get new order
        $order = $position === 'last'
            ? $this->getNextOrder($menu->menu_group, $newParentId)
            : 0;

        // Update item
        $result = $this->update($id, [
            'parent_id' => $newParentId,
            'order' => $order
        ]);

        // Reorder siblings if needed
        if ($position === 'first') {
            $this->reorderSiblings($menu->menu_group, $newParentId);
        }

        // Clear cache
        self::$menuCache = [];

        return $result;
    }

    /**
     * Check if menu has permission
     */
    private function hasPermission(string $permission): bool
    {
        // Check with Shield or your auth system
        if (function_exists('has_permission')) {
            return has_permission($permission);
        }

        return true; // Default allow if no permission system
    }

    /**
     * Get next order number
     */
    private function getNextOrder(string $group, ?int $parentId): int
    {
        $query = $this->where('menu_group', $group);

        if ($parentId === null) {
            $query->where('parent_id', null);
        } else {
            $query->where('parent_id', $parentId);
        }

        $lastItem = $query->orderBy('order', 'DESC')->first();

        return $lastItem ? $lastItem->order + 1 : 0;
    }

    /**
     * Check if item is descendant
     */
    private function isDescendant(int $parentId, int $childId): bool
    {
        $children = $this->where('parent_id', $parentId)->findAll();

        foreach ($children as $child) {
            if ($child->id == $childId) {
                return true;
            }

            if ($this->isDescendant($child->id, $childId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reorder siblings
     */
    private function reorderSiblings(string $group, ?int $parentId): void
    {
        $query = $this->where('menu_group', $group);

        if ($parentId === null) {
            $query->where('parent_id', null);
        } else {
            $query->where('parent_id', $parentId);
        }

        $siblings = $query->orderBy('order', 'ASC')->findAll();

        foreach ($siblings as $index => $sibling) {
            $this->update($sibling->id, ['order' => $index]);
        }
    }
}