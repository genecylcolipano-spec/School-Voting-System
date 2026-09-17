<?php

namespace Tests\Feature\Auth;

use App\Support\SessionExpired;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class CsrfMismatchMessageTest extends TestCase
{
    public function test_json_csrf_failure_tells_the_user_to_refresh(): void
    {
        $request = Request::create('/login/verify', 'POST', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException('CSRF token mismatch.'));

        $this->assertSame(419, $response->getStatusCode());
        $this->assertSame(SessionExpired::MESSAGE, $response->getData(true)['message'] ?? null);
        $this->assertStringNotContainsStringIgnoringCase('csrf', (string) $response->getContent());
        $this->assertStringNotContainsStringIgnoringCase('token mismatch', (string) $response->getContent());
    }

    public function test_html_csrf_failure_redirects_with_the_same_human_message(): void
    {
        $this->startSession();

        $request = Request::create('/login/verify', 'POST', server: [
            'HTTP_ACCEPT' => 'text/html',
            'HTTP_REFERER' => 'http://localhost/',
        ]);
        $request->setLaravelSession($this->app['session']->driver());

        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException('CSRF token mismatch.'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(SessionExpired::MESSAGE, session('error'));
    }
}
