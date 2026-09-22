<form method="POST" action="{{ $action }}">@csrf @if($method !== 'POST') @method($method) @endif
<div class="form-group"><label class="form-label">Nama lengkap</label><input class="form-input" name="name" value="{{ old('name',$user?->name) }}" required></div>
<div class="form-group"><label class="form-label">Email</label><input class="form-input" name="email" type="email" value="{{ old('email',$user?->email) }}" required></div>
<div class="form-group"><label class="form-label">Peran</label><select class="form-input" name="role_id" required>@foreach($roles as $role)<option value="{{ $role->id }}" @selected(old('role_id',$user?->role_id)==$role->id)>{{ $role->name }}</option>@endforeach</select></div>
<input type="hidden" name="kyc_status" value="PENDING">
<div class="form-group"><label class="form-label">Password {{ $user ? '(kosongkan jika tidak diubah)' : '' }}</label><input class="form-input" name="password" type="password" {{ $user ? '' : 'required' }} minlength="8">@if(!$user)<button type="button" class="btn btn-outline btn-sm" style="width:max-content" onclick="this.previousElementSibling.value='password'">Gunakan password default</button>@endif</div>
<div style="display:flex;gap:12px;margin-top:20px"><a href="{{ route('users.index') }}" class="btn btn-outline" style="flex:1;justify-content:center">Batal</a><button class="btn btn-primary" type="submit" style="flex:2;justify-content:center">{{ $button }}</button></div></form>
@if($errors->any())<div style="margin-top:16px;color:#b91c1c;font-size:13px">{{ $errors->first() }}</div>@endif
