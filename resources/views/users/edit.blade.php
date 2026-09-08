@extends('layouts.app')
@section('content')
<div class="section-header"><div><span class="section-title">Edit pengguna</span><p style="color:var(--muted);font-size:14px;margin-top:4px">Perbarui informasi akun.</p></div></div>
<div class="card" style="max-width:700px">@include('users.form',['action'=>route('users.update',$user),'method'=>'PUT','button'=>'Simpan perubahan'])</div>
@endsection
