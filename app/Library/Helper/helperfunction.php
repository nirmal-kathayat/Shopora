<?php
if (!function_exists('getPermissionUrl')) {
    function getPermissionUrl($accessUri)
    {
        $html = '<div class="table-permission-list-wrapper">';
        $html .= '<ul>';
        foreach ($accessUri as $url) {
            if ($url == '/*') {
                $html .= '<li>Full Control</li>';
            } else {
                $urlArr = explode('/', $url);
                $action = count($urlArr) > 2 ? ucFirst($urlArr[2]) : 'View';
                $html .= '<li>' . $action . ' ' . ucFirst($urlArr[1]) . '</li>';
            }
        }
        $html .= '</ul>';
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
