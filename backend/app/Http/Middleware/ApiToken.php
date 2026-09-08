<?php
namespace App\Http\Middleware;
use App\Models\User; use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class ApiToken { public function handle(Request $request, Closure $next): Response { $raw = $request->bearerToken(); $token = $raw ? \DB::table('api_tokens')->where('token_hash',hash('sha256',$raw))->first() : null; if(!$token){return response()->json(['message'=>'Unauthenticated'],401);} $user=User::find($token->user_id); if(!$user){return response()->json(['message'=>'Unauthenticated'],401);} $request->setUserResolver(fn()=>$user); \DB::table('api_tokens')->where('id',$token->id)->update(['last_used_at'=>now(),'updated_at'=>now()]); return $next($request); } }
