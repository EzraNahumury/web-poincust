<x-layouts.admin title="Admin & Role" heading="Admin & Hak Akses">
    <x-slot:actions>
        <a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm">+ Tambah Admin</a>
    </x-slot:actions>

    <div class="card-flush overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Nama</th><th>Username / Email</th><th>Role</th><th>Login terakhir</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $user->name }}</td>
                            <td class="text-xs text-slate-500">{{ '@'.$user->username }}<br>{{ $user->email }}</td>
                            <td><span class="badge-blue">{{ $user->role?->label ?? '—' }}</span></td>
                            <td class="text-xs text-slate-500">{{ $user->last_login_at ? jakarta_date($user->last_login_at)->translatedFormat('d M Y, H:i') : 'belum pernah' }}</td>
                            <td><x-status-badge :status="$user->status" /></td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Edit</a>
                                    @if ($user->id !== auth('web')->id())
                                        <form method="POST" action="{{ route('admin.users.status', $user) }}" onsubmit="return confirm('Ubah status admin ini?')">
                                            @csrf @method('PATCH')
                                            <button class="text-sm font-medium text-slate-500 hover:text-slate-700" type="submit">{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $users->links() }}</div>
</x-layouts.admin>
