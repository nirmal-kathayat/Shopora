<?php
if (!function_exists('getPermissionUrl')) {
    function getPermissionUrl($accessUri)
    {
        $grouped = [];

        foreach ($accessUri as $url) {
            if ($url === '/*') {
                return '<span class="table-permission-list-inline">Full Control</span>';
            }

            $urlArr = explode('/', $url);
            $moduleKey = $urlArr[1] ?? 'general';
            $moduleLabel = preg_replace('/([a-z])([A-Z])/', '$1 $2', ucfirst($moduleKey));

            if (count($urlArr) > 2) {
                $action = ucfirst(str_replace('-', ' ', preg_replace('/\{id\}.*$/', '', $urlArr[2])));
            } else {
                $action = 'View';
            }

            if (!isset($grouped[$moduleKey])) {
                $grouped[$moduleKey] = [
                    'label' => $moduleLabel,
                    'actions' => [],
                ];
            }

            if (!in_array($action, $grouped[$moduleKey]['actions'], true)) {
                $grouped[$moduleKey]['actions'][] = $action;
            }
        }

        if (empty($grouped)) {
            return '';
        }

        $html = '<div class="table-permission-list-stacked">';
        foreach ($grouped as $group) {
            $line = e($group['label']) . ': ' . e(implode(', ', $group['actions']));
            $html .= '<div class="permission-module-line">' . $line . '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('getRelatedList')) {
    function getRelatedList($lists)
    {
        $html = '<div class="table-permission-list-wrapper">';
        $html .= '<ul>';
        foreach ($lists as $list) {
            $html .= '<li>' . $list->name . '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }
}
if (!function_exists('userPermissions')) {
    function userPermissions()
    {
        $user = \Auth::guard(config('permission.guard'))->user();
        $roles = $user->roles()->get();
        $rolesId = [];
        foreach ($roles as $role) {
            $rolesId[] = $role->id;
        }

        return \DB::table('role_permissions')
            ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
            ->whereIn('roles.id', $rolesId)
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->select('permissions.id', 'permissions.name', 'permissions.access_uri')
            ->get()->pluck('access_uri')->toArray();
    }
}

if (!function_exists('canAccessUri')) {
    function canAccessUri(?string $uri): bool
    {
        $user = auth()->guard(config('permission.guard'))->user();
        if (!$user || empty($uri)) {
            return false;
        }

        if ($uri === '/*') {
            return $user->checkUrlAllowAccess(url('/'));
        }

        return $user->checkUrlAllowAccess(url($uri));
    }
}

if (!function_exists('canAccessRoute')) {
    function canAccessRoute(?string $routeName): bool
    {
        if (empty($routeName) || !\Illuminate\Support\Facades\Route::has($routeName)) {
            return false;
        }

        $uri = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName)->uri();

        return canAccessUri($uri);
    }
}

if (!function_exists('canAccessAnyRoute')) {
    function canAccessAnyRoute(array $routeNames): bool
    {
        foreach ($routeNames as $routeName) {
            if (canAccessRoute($routeName)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('hasFullControl')) {
    function hasFullControl(): bool
    {
        return canAccessUri('/*');
    }
}

if (!function_exists('can')) {
    function can($url)
    {
        if (empty($url)) {
            return false;
        }

        $user = auth()->guard(config('permission.guard'))->user();
        if (!$user) {
            return false;
        }

        if (!str_starts_with($url, 'http')) {
            return canAccessUri($url);
        }

        return $user->checkUrlAllowAccess($url);
    }
}

// units
if (!function_exists('get_units')) {
    function get_units()
    {
        return  [
            "Ltr" => 'Ltr',
            'Pcs' => "Pcs",
            'Gm' => 'Gm',
            'Ml' => 'Ml',
            "Jar" => 'Jar',
            "Kg" => 'Kg',
            "Bottel" => "Bottel",
            "Box" => 'Box',
            "Roll" => "Roll",
            "Sachet" => 'Sachet',
            "Unit" => 'Unit',
            "Packet" => 'Packet'
        ];
    }
}
