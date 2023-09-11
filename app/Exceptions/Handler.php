<?php

namespace App\Exceptions;

use Error;
use Exception;
use Throwable;
use TypeError;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request , Throwable $e ){

        if($e instanceof TypeError){
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),422 );
        }
        if($e instanceof MethodNotAllowedException){
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),500 );
        }
        if($e instanceof Exception){
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),500 );
        }
        if($e instanceof Error){
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),500 );
        }

        if(config('app.debug')){
            return parent::render($request , $e);
        }
        DB::rollBack();
        return $this->errorResponse($e->getMessage(), 500 );

    }
}
