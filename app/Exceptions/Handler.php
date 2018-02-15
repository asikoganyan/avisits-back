<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if($this->isHttpException($e))
        {
            switch ($e->getStatusCode())
            {
                case 400:
                    return response()->json(["status"=>"ERROR","message"=>"Bad Request","message"=>"The path info doesn't have the right format, or a parameter or request body value doesn't have the right format, or a required parameter is missing, or values have the right format but are invalid in some way"],400);
                case 404:
                    return response()->json(["status"=>"ERROR","message"=>"Not Found","description"=>"The object referenced by the path does not exist."],404);
                case 405:
                    return response()->json(["status"=>"ERROR","message"=>"Method Not Allowed", "description"=>"Method '".$request->method()."' not allowed on path '".$request->path()."'."],405);
                case 500:
                    return response()->json(["status"=>"ERROR","message"=>"Internal Server Error", "description"=>"The execution of the service failed in some way."],405);
                default:
                    return response()->json(["ExceptionHandler"=>"unhandled error"],400);
            }
        }
        return response()->json(["ExceptionHandler"=>$e->getMessage()],400);
        return parent::render($request, $exception);
    }
}
