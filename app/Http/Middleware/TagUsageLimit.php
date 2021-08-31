<?php

namespace App\Http\Middleware;

use App\Models\Tag;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class TagUsageLimit
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if(Tag::query()->where('user_id',Auth()->id())->count('id') >= 25){
            return $this->error('errors.tag.usage.limit');
        }
        return $next($request);
    }
}
