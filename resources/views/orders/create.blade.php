@extends('layouts.app')
@section('content')
<style>
  .card > div[style*="font-size:32px"] { font-size:0!important; }
  .card > div[style*="font-size:32px"]::before { font-family:'bootstrap-icons'; font-size:32px; color:#2563eb; }
  .card > div[style*="font-size:32px"]::before { content:'\f1c1'; }
  .card > div[style*="font-size:32px"] + h4 + p { color:var(--muted); }
  form > .card h4 { font-size:0!important; }
  form > .card h4::before { font-family:'bootstrap-icons'; font-size:17px; color:#2563eb; margin-right:8px; }
  form > .card h4::after { font-size:15px; color:var(--dark); }
  form > .card:nth-of-type(1) h4::before { content:'\f4c5'; }
  form > .card:nth-of-type(1) h4::after { content:'Lokasi'; }
  form > .card:nth-of-type(2) h4::before { content:'\f1c1'; }
  form > .card:nth-of-type(2) h4::after { content:'Barang'; }
  form > .card:nth-of-type(3) h4::before { content:'\f2db'; }
  form > .card:nth-of-type(3) h4::after { content:'Biaya Estimasi'; }
</style>
<div class="section-header">
  <div>
    <span class="section-title">Buat Pesanan</span>
    <p style="color:var(--muted);font-size:14px;margin-top:4px;">Isi detail barang untuk dikirim oleh kurir atau traveler</p>
  </div>
</div>

<!-- Step Indicator -->
<div class="steps">
  <div class="step active">
    <span class="step-num">1</span>
    <span>Rincian</span>
  </div>
  <div class="step-line"></div>
  <div class="step">
    <span class="step-num">2</span>
    <span>Kirim</span>
  </div>
  <div class="step-line"></div>
  <div class="step">
    <span class="step-num">3</span>
    <span>Selesai</span>
  </div>
</div>

<!-- Pesanan Type Selection -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:28px;">
  <div class="card" style="border:2px solid var(--red);cursor:pointer;position:relative;">
    <span class="badge badge-red" style="position:absolute;top:12px;right:12px;">aktif</span>
    <div style="font-size:32px;margin-bottom:8px;">📦</div>
    <h4 style="font-size:16px;font-weight:700;margin-bottom:4px;">Antar Warga</h4>
    <p style="color:var(--muted);font-size:13px;">Kurir lokal beli & kirim barang</p>
  </div>
  <div class="card" style="cursor:pointer;opacity:.6;">
    <div style="font-size:32px;margin-bottom:8px;">✈️</div>
    <h4 style="font-size:16px;font-weight:700;margin-bottom:4px;">Pra-pesan Internasional</h4>
    <p style="color:var(--muted);font-size:13px;">Pilih traveler dari trip yang tersedia</p>
  </div>
</div>

<!-- Form -->
<form>
  <div class="card" style="margin-bottom:16px;">
    <h4 style="font-size:15px;font-weight:700;margin-bottom:16px;">📍 Lokasi</h4>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Asal (zona)</label>
        <input type="text" class="form-input" placeholder="JAKARTA" value="JAKARTA">
      </div>
      <div class="form-group">
        <label class="form-label">Tujuan (zona)</label>
        <input type="text" class="form-input" placeholder="BANDUNG">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Alamat Penjemputan</label>
      <input type="text" class="form-input" placeholder="Jl. Sudirman No.1, Jakarta Pusat">
    </div>
    <div class="form-group">
      <label class="form-label">Alamat Pengiriman</label>
      <input type="text" class="form-input" placeholder="Jl. Buah Batu No.42, Bandung">
    </div>
  </div>

  <div class="card" style="margin-bottom:16px;">
    <h4 style="font-size:15px;font-weight:700;margin-bottom:16px;">📦 Barang</h4>
    <div class="form-group">
      <label class="form-label">Deskripsi Barang</label>
      <input type="text" class="form-input" placeholder="Laptop ASUS ROG, 1 unit">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Berat (lbs)</label>
        <input type="number" class="form-input" placeholder="2.5" step="0.1" min="0.1">
      </div>
      <div class="form-group">
        <label class="form-label">Jumlah Item</label>
        <input type="number" class="form-input" placeholder="1" value="1" min="1">
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:16px;">
    <h4 style="font-size:15px;font-weight:700;margin-bottom:16px;">💳 Biaya Estimasi</h4>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div style="padding:14px;background:var(--bg);border-radius:10px;">
        <div style="font-size:12px;color:var(--muted);">Ongkir</div>
        <div style="font-size:18px;font-weight:800;">Rp 0</div>
        <div style="font-size:11px;color:var(--muted);">berat × rate + km × rate</div>
      </div>
      <div style="padding:14px;background:var(--bg);border-radius:10px;">
        <div style="font-size:12px;color:var(--muted);">Platform Biaya</div>
        <div style="font-size:18px;font-weight:800;color:var(--red);">Rp 1.000</div>
        <div style="font-size:11px;color:var(--muted);">per transaksi</div>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:12px;">
    <a href="{{ url('/orders') }}" class="btn btn-outline" style="flex:1;justify-content:center;">Batal</a>
    <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center;">Buat Pesanan Sekarang</button>
  </div>
</form>
@endsection
