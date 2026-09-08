<?php

namespace App\Http\Controllers;

use App\Models\EWallet;
use App\Models\Role;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Trip;

class WebAuthController extends Controller
{
    public function login() { return view('auth.login'); }
    public function authenticate(Request $request) { $credentials = $request->validate(['email'=>'required|email','password'=>'required|string']); if (!Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email'=>'Email atau password salah.'])->withInput(); $request->session()->regenerate(); ActivityLog::create(['user_id'=>auth()->id(),'action'=>'LOGIN','description'=>'Login ke platform']); return redirect()->intended('/')->with('success', 'Selamat datang kembali, '.auth()->user()->name.'!'); }
    public function signup() { return view('auth.signup'); }
    public function register(Request $request) { $data = $request->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users,email','password'=>'required|string|min:8|confirmed']); $user=User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password'],'role_id'=>Role::where('name','USER')->value('id')]); EWallet::create(['user_id'=>$user->id]); Auth::login($user); ActivityLog::create(['user_id'=>$user->id,'action'=>'SIGNUP','description'=>'Membuat akun baru']); $request->session()->regenerate(); return redirect('/')->with('success', 'Akun berhasil dibuat. Selamat datang, '.$user->name.'!'); }
    public function logout(Request $request) { if (auth()->check()) ActivityLog::create(['user_id'=>auth()->id(),'action'=>'LOGOUT','description'=>'Logout dari platform']); Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/')->with('success', 'Kamu berhasil logout.'); }
    public function softDeleteAccount(Request $request) { $user=$request->user(); ActivityLog::create(['user_id'=>$user->id,'action'=>'ACCOUNT_SOFT_DELETE','description'=>'Menonaktifkan akun sementara','entity_type'=>'User','entity_id'=>$user->id]); Auth::logout(); $user->delete(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/')->with('success','Akun dinonaktifkan sementara dan dapat dipulihkan oleh administrator.'); }
    public function hardDeleteAccount(Request $request) { $user=$request->user(); if ($user->role?->name === 'SUPER_ADMIN') return back()->with('error','Akun Super Admin tidak dapat dihapus permanen dari halaman ini.'); $request->validate(['current_password'=>'required|current_password']); if (Order::where('customer_id',$user->id)->orWhere('courier_id',$user->id)->orWhere('traveler_id',$user->id)->exists() || Trip::where('traveler_id',$user->id)->exists()) return back()->with('error','Akun masih terhubung dengan pesanan atau perjalanan. Gunakan hapus sementara atau selesaikan data terkait terlebih dahulu.')->withInput(); ActivityLog::create(['user_id'=>$user->id,'action'=>'ACCOUNT_HARD_DELETE','description'=>'Menghapus akun secara permanen','entity_type'=>'User','entity_id'=>$user->id]); Auth::logout(); $user->forceDelete(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/')->with('success','Akun dan data personalnya telah dihapus permanen.'); }
}
