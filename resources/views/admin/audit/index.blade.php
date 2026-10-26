<x-layouts.admin title="Audit Log" heading="Audit Log">
    <div class="card mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1">
                <label class="label">Aksi</label>
                <input name="action" value="{{ $action }}" list="actions" placeholder="mis. transaction.create" class="input">
                <datalist id="actions">
                    @foreach ($actions as $a)<option value="{{ $a }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="label">Aktor</label>
                <select name="actor_type" class="input">
                    <option value="">Semua</option>
                    <option value="admin" @selected($actorType === 'admin')>Admin</option>
                    <option value="customer" @selected($actorType === 'customer')>Customer</option>
                    <option value="system" @selected($actorType === 'system')>System</option>
                </select>
            </div>
            <button class="btn-dark" type="submit">Filter</button>
            <a href="{{ route('admin.audit.index') }}" class="btn-ghost">Reset</a>
        </form>
    </div>

    <div class="card-flush overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Waktu</th><th>Aktor</th><th>Aksi</th><th>Entitas</th><th>Alasan</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-xs text-slate-500">{{ jakarta_date($log->created_at)->translatedFormat('d M Y, H:i:s') }}</td>
                            <td class="text-xs"><span class="badge-slate">{{ $log->actor_type }}</span>@if ($log->actor_id)<span class="text-slate-400"> #{{ $log->actor_id }}</span>@endif</td>
                            <td class="font-medium text-slate-700">{{ $log->action }}</td>
                            <td class="text-xs text-slate-500">{{ $log->entity_type }}{{ $log->entity_id ? ' #'.$log->entity_id : '' }}</td>
                            <td class="max-w-[16rem] truncate text-xs text-slate-500">{{ $log->reason }}</td>
                            <td class="text-xs text-slate-400">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-slate-400">Belum ada aktivitas tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $logs->links() }}</div>
</x-layouts.admin>
