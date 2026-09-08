<?php
use Illuminate\Support\Facades\Route;
Route::view('/', 'home');
Route::view('/services', 'services');
Route::view('/orders', 'orders.index');
Route::view('/orders/create', 'orders.create');
Route::view('/wallet', 'wallet');
Route::view('/wallet/top-up', 'wallet');
Route::view('/trips', 'trips');
Route::view('/shipping/quote', 'services');
Route::get('/dashboard/{role}', function (string $role) { abort_unless(in_array($role, ['user','courier','traveler','admin','operator','super-admin'], true), 404); return view('dashboard', ['role' => strtoupper(str_replace('-', ' ', $role))]); });
