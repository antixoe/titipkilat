@extends('layouts.app')
@section('content')
@php
  $panels = [
    'USER' => [
      'icon' => '👤', 'color' => 'var(--red)', 'bg' => 'var(--red-light)',
      'stats' => [['icon' => '📋', 'label' => 'Total Pesanan', 'value' => '0', 'bg' => 'var(--red-light)', 'color' => 'var(--red)'], ['icon' => '💰', 'label' => 'Saldo Dompet', 'value' => 'Rp 0', 'bg' => 'var(--green-light)', 'color' => 'var(--green)']],
      'actions' => [['icon' => '📦', 'label' => 'Buat Pesanan Antar Warga', 'desc' => 'Ambil dan kirim barang lokal', 'url' => '/orders/create', 'badge' => 'LOKAL', 'badgeClass' => 'badge-red'], ['icon' => '✈️', 'label' => 'Pra-pesan Internasional', 'desc' => 'Pilih traveler & buat PO', 'url' => '/trips', 'badge' => 'GLOBAL', 'badgeClass' => 'badge-orange'], ['icon' => '🧮', 'label' => 'Buat Pesanan Baru', 'desc' => 'Mulai pengiriman dan lihat estimasi biaya', 'url' => '/orders/create', 'badge' => 'MULAI', 'badgeClass' => 'badge-green'], ['icon' => '💰', 'label' => 'Top Up Dompet', 'desc' => 'Isi saldo untuk pembayaran', 'url' => '/wallet', 'badge' => 'BIAYA Rp1K', 'badgeClass' => 'badge-orange']],
    ],
    'COURIER' => [
      'icon' => '🚚', 'color' => 'var(--orange)', 'bg' => 'var(--orange-light)',
      'stats' => [['icon' => '⚡', 'label' => 'Pesanan Tersedia', 'value' => '0', 'bg' => 'var(--orange-light)', 'color' => 'var(--orange)'], ['icon' => '✅', 'label' => 'Selesai Hari Ini', 'value' => '0', 'bg' => 'var(--green-light)', 'color' => 'var(--green)']],
      'actions' => [['icon' => '⚡', 'label' => 'Ambil pesanan', 'desc' => 'Ambil pesanan yang tersedia (kunci basis data)', 'url' => '/orders', 'badge' => 'LANGSUNG', 'badgeClass' => 'badge-red'], ['icon' => '🧾', 'label' => 'Unggah faktur / OCR', 'desc' => 'Foto struk belanja & proses OCR', 'url' => '/orders', 'badge' => 'BARU', 'badgeClass' => 'badge-orange'], ['icon' => '🚚', 'label' => 'Perbarui pengiriman', 'desc' => 'Ubah status pengiriman', 'url' => '/orders', 'badge' => 'STATUS', 'badgeClass' => 'badge-slate'], ['icon' => '💰', 'label' => 'Cek pendapatan', 'desc' => 'Biaya Rp 1.000 per item', 'url' => '/wallet', 'badge' => 'Rp 1K/item', 'badgeClass' => 'badge-green']],
    ],
    'TRAVELER' => [
      'icon' => '✈️', 'color' => 'var(--orange)', 'bg' => 'var(--orange-light)',
      'stats' => [['icon' => '🌍', 'label' => 'Perjalanan Aktif', 'value' => '0', 'bg' => 'var(--orange-light)', 'color' => 'var(--orange)'], ['icon' => '📋', 'label' => 'PO Diterima', 'value' => '0', 'bg' => 'var(--red-light)', 'color' => 'var(--red)']],
      'actions' => [['icon' => '🌍', 'label' => 'Buat Jadwal Perjalanan', 'desc' => 'Buka pra-pesan internasional baru', 'url' => '/trips', 'badge' => 'BARU', 'badgeClass' => 'badge-orange'], ['icon' => '📋', 'label' => 'Kelola Pra-pesan', 'desc' => 'Terima & proses PO dari user', 'url' => '/orders', 'badge' => 'PO', 'badgeClass' => 'badge-red'], ['icon' => '📸', 'label' => 'Upload Foto Pembelian', 'desc' => 'Bukti beli barang di luar negeri', 'url' => '/orders', 'badge' => 'BUKTI', 'badgeClass' => 'badge-green'], ['icon' => '📦', 'label' => 'Kirim Barang', 'desc' => 'Update pengiriman lintas negara', 'url' => '/orders', 'badge' => 'PENGIRIMAN', 'badgeClass' => 'badge-slate']],
    ],
    'ADMIN' => [
      'icon' => '🛡️', 'color' => 'var(--red)', 'bg' => 'var(--red-light)',
      'stats' => [['icon' => '📊', 'label' => 'Transaksi Aktif', 'value' => '0', 'bg' => 'var(--red-light)', 'color' => 'var(--red)'], ['icon' => '⚠️', 'label' => 'Sengketa terbuka', 'value' => '0', 'bg' => 'var(--orange-light)', 'color' => 'var(--orange)']],
      'actions' => [['icon' => '📊', 'label' => 'Transaksi Aktif', 'desc' => 'Monitor semua pesanan aktif', 'url' => '/dashboard/admin', 'badge' => 'AKTIF', 'badgeClass' => 'badge-red'], ['icon' => '⚖️', 'label' => 'Review Sengketa', 'desc' => 'Selesaikan konflik antar user', 'url' => '/dashboard/admin', 'badge' => 'PENTING', 'badgeClass' => 'badge-orange'], ['icon' => '🪪', 'label' => 'Verifikasi verifikasi identitas', 'desc' => 'Setujui atau tolak identitas', 'url' => '/dashboard/admin', 'badge' => 'verifikasi identitas', 'badgeClass' => 'badge-green'], ['icon' => '📈', 'label' => 'Laporan', 'desc' => 'Statistik transaksi & revenue', 'url' => '/dashboard/admin', 'badge' => 'DATA', 'badgeClass' => 'badge-slate']],
    ],
    'SUPER ADMIN' => [
      'icon' => '⚙️', 'color' => 'var(--dark)', 'bg' => 'var(--bg)',
      'stats' => [['icon' => '💰', 'label' => 'Total saldo dompet', 'value' => 'Rp 0', 'bg' => 'var(--green-light)', 'color' => 'var(--green)'], ['icon' => '👥', 'label' => 'Total pengguna', 'value' => '0', 'bg' => 'var(--red-light)', 'color' => 'var(--red)']],
      'actions' => [['icon' => '🏦', 'label' => 'Audit Buku kas', 'desc' => 'Cek saldo semua dompet secara transparan', 'url' => '/dashboard/super-admin', 'badge' => 'LEDGER', 'badgeClass' => 'badge-red'], ['icon' => '⚙️', 'label' => 'Konfigurasi Biaya', 'desc' => 'Ubah biaya platform, kurir, ongkir', 'url' => '/dashboard/super-admin', 'badge' => 'BIAYA', 'badgeClass' => 'badge-orange'], ['icon' => '📡', 'label' => 'Monitoring Sistem', 'desc' => 'Statistik performa & health check', 'url' => '/dashboard/super-admin', 'badge' => 'SISTEM', 'badgeClass' => 'badge-green'], ['icon' => '👥', 'label' => 'Manajemen Pengguna', 'desc' => 'Kelola semua pengguna & peran', 'url' => '/dashboard/super-admin', 'badge' => 'Akses peran', 'badgeClass' => 'badge-slate']],
    ],
  ];
  $panel = $panels[$role] ?? $panels['USER'];
@endphp

<!-- Peran Header -->
<div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
  <div style="width:56px;height:56px;border-radius:16px;background:{{ $panel['bg'] }};display:flex;align-items:center;justify-content:center;font-size:28px;">{{ $panel['icon'] }}</div>
  <div>
    <span class="badge badge-red" style="margin-bottom:4px;">{{ $role }}</span>
    <h1 style="font-size:24px;font-weight:800;">Dasbor {{ ucwords(strtolower(str_replace('_', ' ', $role))) }}</h1>
    <p style="color:var(--muted);font-size:14px;">Kelola aktivitas Titip Kilat dengan cepat dan aman</p>
  </div>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:24px;">
  @foreach($panel['stats'] as $stat)
    <div class="stat-card">
      <div class="stat-icon" style="background:{{ $stat['bg'] }};color:{{ $stat['color'] }};">{{ $stat['icon'] }}</div>
      <div>
        <div class="stat-value">{{ $stat['value'] }}</div>
        <div class="stat-label">{{ $stat['label'] }}</div>
      </div>
    </div>
  @endforeach
</div>

<!-- Actions -->
<div class="section-header">
  <span class="section-title">Modul Aktif</span>
</div>
<div class="card-grid">
  @foreach($panel['actions'] as $action)
    <a href="{{ url($action['url']) }}" class="card" style="text-decoration:none;color:inherit;display:block;border-left:3px solid var(--red);">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
        <span class="badge {{ $action['badgeClass'] }}">{{ $action['badge'] }}</span>
        <span style="font-size:24px;">{{ $action['icon'] }}</span>
      </div>
      <h4 style="font-size:15px;font-weight:700;margin-bottom:4px;">{{ $action['label'] }}</h4>
      <p style="color:var(--muted);font-size:13px;">{{ $action['desc'] }}</p>
    </a>
  @endforeach
</div>
@endsection
