@extends('layouts.app')
@section('content')
<div class="section-header"><div><span class="section-title">Tambah pengguna</span><p style="color:var(--muted);font-size:14px;margin-top:4px">Buat akun baru untuk platform.</p></div></div>
<div class="card" style="max-width:700px">@include('users.form',['user'=>null,'action'=>route('users.store'),'method'=>'POST','button'=>'Simpan pengguna'])</div>
@endsection
