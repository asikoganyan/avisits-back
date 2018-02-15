<?php

namespace App\Http\Middleware;

use App\Models\Chain;
use App\Models\Salon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSalon
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $salonId = $request->route('salon') ? (integer)$request->route('salon') : null;
        $chainId = $request->route('chain') || null;
        if($salonId !== null){
            $salon = Salon::where(['user_id'=>Auth::id(),'id'=>$salonId])->count();
            if($salon !== 0){
                return $next($request);
            }
        }
        return $next($request);
        return response()->json(['error'=>'incorrect Salon ID'],400);
    }
}
