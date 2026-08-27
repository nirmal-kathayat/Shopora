<?php

namespace App\Http\Controllers;

use IAnanta\UserManagement\Models\Permission;
use IAnanta\UserManagement\Repository\PermissionRepository;
use App\Http\Requests\PermissionRequest;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    private $repo;
    public function __construct(PermissionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $data = Permission::query()->orderBy('created_at', 'asc');
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->editColumn('access_uri', function ($query) {
                        if (!empty($query->access_uri))
                            return getPermissionUrl($query->access_uri);
                        else
                            return '';
                    })

                    ->rawColumns(['access_uri'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
        return view('permission.index');
    }


    public function create()
    {
        try {
            $data['routeLists'] = (new Permission)->routePermissionList();
            return view('permission.form')->with($data);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function store(PermissionRequest $request)
    {
        try {
            $this->repo->storePermission($request->validated());
            return redirect()->route('admin.permission')->with(['message' => 'Permission created successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $data['routeLists'] = (new Permission)->routePermissionList();
            $data['permission'] = $this->repo->findPermission($id);
            return view('permission.form')->with($data);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function update(PermissionRequest $request, $id)
    {
        try {
            $this->repo->updatePermission($request->validated(), $id);
            return redirect()->route('admin.permission')->with(['message' => 'Permission updated successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->repo->deletePermission($id);
            return response()->json(['message' => 'Permission deleted successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }
}
