<?php

namespace App\Http\Middleware;

use App\Models\Chain;
use App\Models\Salon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckChain
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
        $chainId = $request->route('chain') ? (integer)$request->route('chain') : null;
        if($chainId!==null){
            $chain = Chain::where(['user_id'=>Auth::id(),'id'=>$chainId])->count();
            if($chain !== 0){
                return $next($request);
            }
        }
        return response()->json(['error'=>'incorrect chain'],400);
    }
}
