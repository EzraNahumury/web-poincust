<x-layouts.admin title="Ulang Tahun" heading="Pengingat Ulang Tahun">
    <div class="card mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px] flex-1">
                <label class="label">Cari nama</label>
                <input name="q" value="{{ $search }}" placeholder="Nama customer" class="input">
            </div>
            <div>
                <label class="label">Rentang (hari ke depan)</label>
                <select name="days" class="input">
                    @foreach ([7, 14, 30] as $d)
                        <option value="{{ $d }}" @selected($days === $d)>{{ $d }} hari</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-dark" type="submit">Terapkan</button>
        </form>
        <p class="mt-3 text-xs text-slate-500">Zona waktu Asia/Jakarta. Membuka WhatsApp tidak berarti pesan terkirim — tandai manual setelah benar-benar menghubungi.</p>
    </div>

    {{-- Today --}}
    <h2 class="mb-3 flex items-center gap-2 text-lg font-bold text-slate-900">🎂 Hari ini <span class="badge-brand">{{ $today->count() }}</span></h2>
    @include('admin.birthdays._list', ['rows' => $today, 'emptyText' => 'Tidak ada yang berulang tahun hari ini.'])

    {{-- Upcoming --}}
    <h2 class="mb-3 mt-8 flex items-center gap-2 text-lg font-bold text-slate-900">📅 {{ $days }} hari ke depan <span class="badge-slate">{{ $upcoming->count() }}</span></h2>
    @include('admin.birthdays._list', ['rows' => $upcoming, 'emptyText' => 'Tidak ada ulang tahun dalam rentang ini.'])
</x-layouts.admin>
