<?php
if (!function_exists('getPermissionUrl')) {
    function getPermissionUrl($accessUri)
    {
        $labels = [];

        foreach ($accessUri as $url) {
            if ($url === '/*') {
                $labels[] = 'Full Control';
                continue;
            }

            $urlArr = explode('/', $url);
            $module = preg_replace('/([a-z])([A-Z])/', '$1 $2', ucfirst($urlArr[1] ?? ''));

            if (count($urlArr) > 2) {
                $action = ucfirst(str_replace('-', ' ', preg_replace('/\{id\}.*$/', '', $urlArr[2])));
                $labels[] = trim($action . ' ' . $module);
            } else {
                $labels[] = trim('View ' . $module);
            }
        }

        return '<span class="table-permission-list-inline">' . e(implode(', ', $labels)) . '</span>';
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

if (!function_exists('can')) {
    function can($url)
    {
        if (!empty($url)) {
            return auth()->guard('admin')->user()->checkUrlAllowAccess($url);
        }
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
