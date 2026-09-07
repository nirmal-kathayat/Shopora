<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use IAnanta\UserManagement\Repository\AdminRepository;
use IAnanta\UserManagement\Repository\RoleRepository;
use App\Http\Requests\UserRequest;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    private $repo, $roleRepo;
    public function __construct(AdminRepository $repo, RoleRepository $roleRepo)
    {
        $this->repo = $repo;
        $this->roleRepo = $roleRepo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $data = Admin::query()->orderBy('created_at', 'asc');
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->rawColumns([])
                    ->make(true);
            }
            return view('user.index');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function create()
    {
        try {
            $data['roles'] = $this->roleRepo->getRoles();
            return view('user.form')->with($data);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function store(UserRequest $request)
    {
        try {
            $data = $request->validated();

            // Written out rather than $this->repo->storeAdmin(): that one
            // hands the whole validated payload to create(), the roles array
            // with it, and the model guards nothing - so MySQL is asked to
            // insert a `roles` column that does not exist and the save blows
            // up. It also assembles a created_by and then discards it.
            $admin = Admin::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                // Hashed by the model's password mutator.
                'password' => $data['password'],
                'created_by' => auth()->guard(config('permission.guard'))->id(),
            ]);

            $admin->roles()->sync($data['roles'] ?? []);
            Admin::clearPermissionCache($admin->id);

            return redirect()->route('admin.user')->with(['message' => 'User created successfully', 'type' => 'success']);
        } catch (\Throwable $e) {
            \Log::error('User create failed: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }


    public function edit($id)
    {
        try {
            $data['roles'] = $this->roleRepo->getRoles();
            $data['user'] = $this->repo->findAdmin($id);
            return view('user.form')->with($data);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function update(UserRequest $request, $id)
    {
        try {
            $admin = $this->repo->updateAdmin($request->validated(), $id);
            $admin->roles()->sync($request->input('roles', []));
            Admin::clearPermissionCache((int) $id);
            return redirect()->route('admin.user')->with(['message' => 'User updated successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->repo->deleteAdminForever($id);
            return response()->json(['message' => 'User deleted successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the menu.']);
        }
    }

    public function changePassword()
    {
        try {
            $auth = \Auth::guard('admin')->user();
            if (\Hash::check(request()->get('current_password'), $auth->password)) {
                $newPassword =  \Hash::make(request()->new_password);
                Admin::where('id', $auth->id)->update([
                    'password' => $newPassword
                ]);
                return redirect()
                    ->back()->with(['message' => 'Password changes successfully', 'type' => 'success']);
            } else {
                return redirect()
                    ->back()->with(['message' => 'Current password did not match', 'type' => 'error']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Somthing were wrong', 'type' => 'error']);
        }
    }
}
