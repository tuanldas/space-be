<?php

namespace App\Http\Middleware;

use Closure;
use Silber\Bouncer\Bouncer;

class ScopeBouncer
{
    /**
     * The Bouncer instance.
     *
     * @var \Silber\Bouncer\Bouncer
     */
    protected $bouncer;

    /**
     * Constructor.
     */
    public function __construct(Bouncer $bouncer)
    {
        $this->bouncer = $bouncer;
    }

    /**
     * Set the proper Bouncer scope for the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Hiện tại chúng ta không cần scope theo tenant
        // Nếu sau này cần tính năng multi-tenant, hãy bổ sung logic ở đây
        // $tenantId = $request->user()->tenant_id;
        // $this->bouncer->scope()->to($tenantId);

        return $next($request);
    }
}
