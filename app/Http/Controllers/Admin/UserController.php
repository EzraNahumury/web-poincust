<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by cast
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        AuditLogger::log('user.create', [
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'after' => ['username' => $user->username, 'role_id' => $user->role_id, 'status' => $user->status],
        ]);

        return redirect()->route('admin.users.index')->with('success', "Admin {$user->username} dibuat.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // A super admin must not lock themselves out of their own role.
        if ($user->id === Auth::guard('web')->id() && (int) $data['role_id'] !== (int) $user->role_id) {
            return back()->withErrors(['role_id' => 'Anda tidak dapat mengubah role akun Anda sendiri.'])->withInput();
        }

        $user->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        AuditLogger::log('user.update', [
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'after' => ['username' => $user->username, 'role_id' => $user->role_id, 'status' => $user->status],
        ]);

        return redirect()->route('admin.users.index')->with('success', "Admin {$user->username} diperbarui.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::guard('web')->id()) {
            return back()->withErrors(['status' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        AuditLogger::log('user.status_change', [
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'after' => ['status' => $user->status],
        ]);

        return back()->with('success', "Status admin {$user->username}: {$user->status}.");
    }
}
