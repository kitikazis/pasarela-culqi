<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdEditTest extends TestCase
{
    use RefreshDatabase;

    private function adFor(User $user, array $overrides = []): Ad
    {
        return $user->ads()->create(array_merge([
            'categoria'    => 'venta',
            'descripcion'  => 'Vendo bicicleta <usada>',
            'telefono'     => '987654321',
            'cobertura'    => 'distrital',
            'departamento' => 'Lima',
            'provincia'    => 'Lima',
            'distrito'     => 'Miraflores',
            'estado'       => 'active',
            'vistas'       => 7,
        ], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'category'    => 'trabajo',
            'description' => 'Busco ayudante de cocina',
            'phone'       => '912345678',
            'coverage'    => 'nacional',
            // Datos sobrantes del formulario: con cobertura nacional no deben guardarse.
            'department'  => 'Cusco',
            'province'    => 'Cusco',
            'district'    => 'Cusco',
        ], $overrides);
    }

    public function test_el_dueno_lee_su_anuncio_sin_escapar_para_el_formulario(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);

        $this->actingAs($user)
            ->getJson("/anuncios/{$ad->id}/editar")
            ->assertOk()
            ->assertJson([
                'id'          => $ad->id,
                'category'    => 'venta',
                'description' => 'Vendo bicicleta <usada>',
                'phone'       => '987654321',
                'coverage'    => 'distrital',
                'department'  => 'Lima',
                'province'    => 'Lima',
                'district'    => 'Miraflores',
            ]);
    }

    public function test_otro_usuario_no_lee_ni_edita_el_anuncio(): void
    {
        $ad   = $this->adFor(UserFactory::new()->create());
        $otro = UserFactory::new()->create();

        $this->actingAs($otro)->getJson("/anuncios/{$ad->id}/editar")->assertForbidden();
        $this->actingAs($otro)->putJson("/anuncios/{$ad->id}", $this->payload())->assertForbidden();

        $this->assertSame('Vendo bicicleta <usada>', $ad->fresh()->descripcion);
    }

    public function test_sin_sesion_no_se_puede_editar(): void
    {
        $ad = $this->adFor(UserFactory::new()->create());

        $this->getJson("/anuncios/{$ad->id}/editar")->assertForbidden();
        $this->putJson("/anuncios/{$ad->id}", $this->payload())->assertForbidden();
    }

    public function test_el_dueno_edita_y_la_ubicacion_sigue_la_cobertura(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);

        $this->actingAs($user)->putJson("/anuncios/{$ad->id}", $this->payload())->assertOk();

        $ad->refresh();
        $this->assertSame('trabajo', $ad->categoria);
        $this->assertSame('Busco ayudante de cocina', $ad->descripcion);
        $this->assertSame('912345678', $ad->telefono);
        $this->assertSame('nacional', $ad->cobertura);
        $this->assertNull($ad->departamento);
        $this->assertNull($ad->provincia);
        $this->assertNull($ad->distrito);
    }

    public function test_editar_no_gasta_credito_y_funciona_con_cero_creditos(): void
    {
        $user = UserFactory::new()->create(['publish_credits' => 0]);
        $ad   = $this->adFor($user);

        $this->actingAs($user)->putJson("/anuncios/{$ad->id}", $this->payload())->assertOk();

        $this->assertSame(0, $user->fresh()->publish_credits);
        $this->assertSame('trabajo', $ad->fresh()->categoria);
    }

    public function test_editar_conserva_fecha_vistas_destacado_y_estado(): void
    {
        $user      = UserFactory::new()->create();
        $destacado = now()->addDays(3)->startOfSecond();
        $ad        = $this->adFor($user, ['estado' => 'inactive', 'destacado_hasta' => $destacado]);
        $creado    = $ad->fresh()->created_at;

        $this->travel(2)->days();
        $this->actingAs($user)->putJson("/anuncios/{$ad->id}", $this->payload())->assertOk();

        $ad->refresh();
        $this->assertSame('inactive', $ad->estado);
        $this->assertSame(7, $ad->vistas);
        $this->assertTrue($creado->equalTo($ad->created_at));
        $this->assertTrue($destacado->equalTo($ad->destacado_hasta));
    }

    public function test_la_edicion_pasa_por_la_misma_validacion_y_moderacion(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);
        config(['moderation.profanity' => ['palabrota']]);

        $this->actingAs($user)
            ->putJson("/anuncios/{$ad->id}", $this->payload(['description' => 'vendo palabrota barata']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('description');

        $this->actingAs($user)
            ->putJson("/anuncios/{$ad->id}", $this->payload(['phone' => '123']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');

        $this->assertSame('Vendo bicicleta <usada>', $ad->fresh()->descripcion);
    }

    public function test_un_anuncio_en_papelera_no_se_edita(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);
        $ad->update(['estado' => 'borrado']);
        $ad->delete();

        $this->actingAs($user)->getJson("/anuncios/{$ad->id}/editar")->assertNotFound();
        $this->actingAs($user)->putJson("/anuncios/{$ad->id}", $this->payload())->assertNotFound();
    }
}
