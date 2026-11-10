<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FriendlyErrorsTest extends TestCase
{
    public function test_common_errors_show_plain_guidance_without_reflecting_request_details(): void
    {
        $this->withoutVite();
        config(['app.debug' => false]);
        Route::get('/expired-form/{secret}', fn () => abort(419, 'sensitive message'));
        Route::get('/rate-limit/{secret}', fn () => abort(429, 'sensitive message', ['Retry-After' => '30']));

        $this->get('/missing-private-marker')->assertNotFound()
            ->assertSee('Страница не найдена')->assertDontSee('missing-private-marker');
        $this->get('/expired-form/private-marker')->assertStatus(419)
            ->assertSee('Срок действия формы истёк')->assertDontSee('sensitive message')->assertDontSee('private-marker');
        $this->get('/rate-limit/private-marker')->assertTooManyRequests()->assertHeader('Retry-After', '30')
            ->assertSee('Слишком много запросов')->assertDontSee('sensitive message')->assertDontSee('private-marker');
    }
}
