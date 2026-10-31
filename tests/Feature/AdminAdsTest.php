<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAdsTest extends TestCase
{
    use RefreshDatabase;

    private function adFor(User $user, array $overrides = []): Ad
    {
        return $user->ads()->create(array_merge([
            'categoria'    => 'venta',
            'descripcion'  => 'Vendo bicicleta',
            'telefono'     => '987654321',
            'cobertura'    => 'nacional',
            'estado'       => 'active',
        ], $overrides));
    }

    private function asAdmin(): static
    {
        return $this->withSession(['admin_authenticated' => true]);
    }

    private function editPayload(): array
    {
        return [
            'category'    => 'venta',
            'description' => 'Texto nuevo',
            'phone'       => '987654321',
            'coverage'    => 'nacional',
        ];
    }

    public function test_sin_login_de_panel_las_acciones_redirigen_y_no_cambian_nada(): void
    {
        $ad = $this->adFor(UserFactory::new()->create());

        foreach (['desactivar', 'activar', 'eliminar', 'restaurar', 'definitivo'] as $accion) {
            $this->post("/admin/anuncios/{$ad->id}/{$accion}")->assertRedirect(route('admin.login'));
        }

        $this->assertSame('active', $ad->fresh()->estado);
        $this->assertFalse($ad->fresh()->isRetired());
    }

    public function test_desactivar_retira_el_anuncio_y_el_dueno_no_puede_reactivarlo(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);

        $this->asAdmin()->from('/admin/anuncios?categoria=venta')
            ->post("/admin/anuncios/{$ad->id}/desactivar")
            ->assertRedirect('/admin/anuncios?categoria=venta');

        $ad->refresh();
        $this->assertSame('inactive', $ad->estado);
        $this->assertTrue($ad->isRetired());

        $this->actingAs($user)->patchJson("/anuncios/{$ad->id}", ['status' => 'active'])->assertForbidden();
        $this->assertSame('inactive', $ad->fresh()->estado);
    }

    public function test_el_dueno_no_edita_un_anuncio_retirado(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);
        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/desactivar");

        $this->actingAs($user)->getJson("/anuncios/{$ad->id}/editar")->assertForbidden();
        $this->actingAs($user)->putJson("/anuncios/{$ad->id}", $this->editPayload())->assertForbidden();

        $this->assertSame('Vendo bicicleta', $ad->fresh()->descripcion);
    }

    public function test_activar_revoca_el_retiro_y_el_dueno_recupera_el_control(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);
        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/desactivar");

        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/activar")->assertRedirect();

        $ad->refresh();
        $this->assertSame('active', $ad->estado);
        $this->assertFalse($ad->isRetired());

        $this->actingAs($user)->patchJson("/anuncios/{$ad->id}", ['status' => 'inactive'])->assertOk();
    }

    public function test_eliminar_va_a_papelera_y_el_dueno_no_puede_restaurarlo(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);

        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/eliminar")->assertRedirect();

        $ad = Ad::withTrashed()->find($ad->id);
        $this->assertTrue($ad->trashed());
        $this->assertSame('borrado', $ad->estado);
        $this->assertTrue($ad->isRetired());

        $this->actingAs($user)->patchJson("/anuncios/{$ad->id}/restaurar")->assertForbidden();
        $this->assertTrue($ad->fresh()->trashed());
    }

    public function test_restaurar_lo_retirado_por_el_admin_lo_vuelve_a_publicar(): void
    {
        $ad = $this->adFor(UserFactory::new()->create());
        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/eliminar");

        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/restaurar")->assertRedirect();

        $ad = Ad::find($ad->id);
        $this->assertNotNull($ad);
        $this->assertSame('active', $ad->estado);
        $this->assertFalse($ad->isRetired());
    }

    public function test_restaurar_lo_que_borro_el_dueno_no_lo_republica_sin_su_decision(): void
    {
        $user = UserFactory::new()->create();
        $ad   = $this->adFor($user);
        $this->actingAs($user)->deleteJson("/anuncios/{$ad->id}")->assertOk();

        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/restaurar")->assertRedirect();

        $ad = Ad::find($ad->id);
        $this->assertSame('inactive', $ad->estado);
        $this->assertFalse($ad->isRetired());
    }

    public function test_eliminar_definitivamente_solo_aplica_a_la_papelera(): void
    {
        $ad = $this->adFor(UserFactory::new()->create());

        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/definitivo")->assertNotFound();
        $this->assertNotNull(Ad::find($ad->id));

        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/eliminar");
        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/definitivo")->assertRedirect();

        $this->assertNull(Ad::withTrashed()->find($ad->id));
    }

    public function test_el_listado_separa_los_anuncios_de_la_papelera(): void
    {
        $user = UserFactory::new()->create();
        $this->adFor($user, ['descripcion' => 'Anuncio visible']);
        $borrado = $this->adFor($user, ['descripcion' => 'Anuncio en papelera']);
        $this->asAdmin()->post("/admin/anuncios/{$borrado->id}/eliminar");

        $this->asAdmin()->get('/admin/anuncios')
            ->assertOk()
            ->assertSee('Anuncio visible')
            ->assertDontSee('Anuncio en papelera')
            ->assertSee('Desactivar')
            ->assertSee('Papelera (1)');

        $this->asAdmin()->get('/admin/anuncios?estado=borrado')
            ->assertOk()
            ->assertSee('Anuncio en papelera')
            ->assertDontSee('Anuncio visible')
            ->assertSee('Restaurar')
            ->assertSee('Eliminar definitivamente');
    }

    public function test_el_dashboard_queda_de_solo_lectura(): void
    {
        $this->adFor(UserFactory::new()->create(), ['descripcion' => 'Anuncio visible']);

        $this->asAdmin()->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Anuncio visible')
            ->assertDontSee('/desactivar');
    }

    public function test_mis_anuncios_avisa_al_dueno_que_esta_retirado(): void
    {
        $user    = UserFactory::new()->create();
        $ad      = $this->adFor($user);
        $borrado = $this->adFor($user);
        $this->asAdmin()->post("/admin/anuncios/{$ad->id}/desactivar");
        $this->asAdmin()->post("/admin/anuncios/{$borrado->id}/eliminar");

        $this->actingAs($user)->getJson('/mis-anuncios/datos')
            ->assertOk()
            ->assertJsonPath('ads.0.retired', true);

        $this->actingAs($user)->getJson('/mis-anuncios/papelera')
            ->assertOk()
            ->assertJsonPath('ads.0.retired', true);
    }
}
