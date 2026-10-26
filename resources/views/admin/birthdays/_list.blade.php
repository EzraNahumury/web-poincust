@if ($rows->isEmpty())
    <x-empty title="Kosong" :message="$emptyText" />
@else
    <div class="card-flush overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Customer</th><th>WhatsApp</th><th>Ulang tahun</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php $c = $row['customer']; $f = $row['followup']; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.customers.show', $c) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $c->name }}</a>
                                <p class="text-xs text-slate-500">{{ $c->member_number }}</p>
                            </td>
                            <td class="text-slate-600">{{ $c->maskedWhatsapp() }}</td>
                            <td>
                                <span class="text-slate-700">{{ jakarta_date($row['birthday_on'])->translatedFormat('d M') }}</span>
                                @if ($row['is_today'])
                                    <span class="badge-brand ml-1">hari ini</span>
                                @else
                                    <span class="text-xs text-slate-400">({{ (int) $row['days_until'] }} hari lagi)</span>
                                @endif
                            </td>
                            <td><x-status-badge :status="$f?->status ?? 'pending'" /></td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    @if ($row['wa_link'])
                                        <a href="{{ $row['wa_link'] }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">WhatsApp</a>
                                    @else
                                        <span class="text-xs text-slate-400">No. tidak valid</span>
                                    @endif
                                    @unless ($f && $f->status === 'contacted')
                                        <form method="POST" action="{{ route('admin.birthdays.followup', $c) }}">
                                            @csrf
                                            <button type="submit" class="btn-dark btn-sm">Tandai dihubungi</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
