<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use IAnanta\UserManagement\Models\Admin as VendorAdmin;

class Admin extends VendorAdmin
{
    public function allViewPermissions()
    {
        if (static::$allViewPermissions === null) {
            $arrView = [];
            $permissionRows = static::allPermissions()->pluck('access_uri')->toArray();

            foreach ($permissionRows as $actionList) {
                foreach (explode(',', $actionList) as $action) {
                    $action = trim($action);
                    if ($action === '') {
                        continue;
                    }

                    if ($action === '/*') {
                        $arrView[] = $this->normalizedAllowPath(url('/')) . '/*';
                        continue;
                    }

                    $arrView[] = $this->normalizedAllowPath(url($action));
                    $arrView[] = ltrim($action, '/');
                }
            }

            static::$allViewPermissions = array_values(array_unique($arrView));
        }

        return static::$allViewPermissions;
    }

    public function checkUrlAllowAccess($url)
    {
        $listUrlAllowAccess = $this->allViewPermissions();
        $pathCheck = $this->normalizedAllowPath($url);
        $pathOnly = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $adminWildcard = $this->normalizedAllowPath(url('/')) . '/*';

        if (in_array($adminWildcard, $listUrlAllowAccess, true)) {
            return true;
        }

        foreach ($listUrlAllowAccess as $pathAllow) {
            $allowedPath = ltrim($pathAllow, '/');

            if ($pathCheck === $pathAllow || $pathOnly === $allowedPath) {
                return true;
            }

            if (Str::endsWith($pathAllow, '{id}')) {
                $prefix = str_replace('/{id}', '', $pathAllow);
                $prefixPath = ltrim(str_replace('{id}', '', $allowedPath), '/');

                if (
                    $pathCheck === $prefix
                    || Str::startsWith($pathCheck, $prefix)
                    || Str::startsWith($pathOnly, $prefixPath)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function normalizedAllowPath(string $url): string
    {
        return rtrim(str_replace(['https://', 'http://'], '', $url), '/');
    }

    public static function clearPermissionCache(?int $userId = null): void
    {
        if ($userId !== null) {
            Cache::forget('user-permissions-' . $userId);
        }

        static::$allPermissions = null;
        static::$allViewPermissions = null;
    }

    public static function clearPermissionCacheForRole(int $roleId): void
    {
        DB::table('admin_roles')
            ->where('role_id', $roleId)
            ->pluck('admin_id')
            ->each(function ($adminId) {
                Cache::forget('user-permissions-' . $adminId);
            });

        static::$allPermissions = null;
        static::$allViewPermissions = null;
    }

    public static function clearAllPermissionCaches(): void
    {
        static::query()->pluck('id')->each(function ($adminId) {
            Cache::forget('user-permissions-' . $adminId);
        });

        static::$allPermissions = null;
        static::$allViewPermissions = null;
    }
}
