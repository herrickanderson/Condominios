<?php

use App\Http\Controllers\AdminpagosController;
use App\Http\Controllers\CategoriaGastoComunController;
use App\Http\Controllers\condominiosController;
use App\Http\Controllers\ConfigConsumoController;
use App\Http\Controllers\ConfiguracionPeriodosController;
use App\Http\Controllers\ConfMoraController;
use App\Http\Controllers\ConfPagoController;
use App\Http\Controllers\detalle_gasto_comunController;
use App\Http\Controllers\DistribucionGastoController;
use App\Http\Controllers\edificiosController;
use App\Http\Controllers\ExtencionesController;
use App\Http\Controllers\gastos_comunesController;
use App\Http\Controllers\MedicionConsumoController;
use App\Http\Controllers\pagosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\ProrrateoValoresController;
use App\Http\Controllers\ReporteIngresosController;
use App\Http\Controllers\rolesController;
use App\Http\Controllers\tipo_prorrateoController;
use App\Http\Controllers\TipoGastoComunController;
use App\Http\Controllers\TipoProrrateoCondominioController;
use App\Http\Controllers\unidadesController;
use App\Http\Controllers\UserInfoController;
use App\Http\Controllers\usersController;
use App\Http\Controllers\VigilanciaController;
use App\Models\GastosComune;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

//anterior
/*
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/
Route::get('/dashboard', [AdminpagosController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/user-info', [UserInfoController::class, 'index'])
    ->middleware('auth')
    ->name('user.info');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('Condominios', [condominiosController::class, 'index'])->name('condominios.index');
    Route::get('Condominios/create', [condominiosController::class, 'create'])->name('condominios.create');
    Route::post('condominios', [condominiosController::class, 'store'])->name('condominios.store');

    //CONDOMINIOS
    Route::get('Condominios/{condominio}/edit', [condominiosController::class, 'edit'])->name('condominios.edit');
    //para actualizxar
    Route::post('Condominios/{condominio}', [condominiosController::class, 'update'])->name('condominios.update');
    //para Eliminar Condominios
    Route::delete('Condominios/{condominio}', [condominiosController::class, 'destroy'])->name('condominios.destroy');




    //EDIFICIOS
    Route::get('Edificio', [EdificiosController::class, 'index'])->name('edificios.index');
    Route::get('Edificio/create', [EdificiosController::class, 'create'])->name('edificios.create');
    Route::post('Edificio', [EdificiosController::class, 'store'])->name('edificios.store');
    // Para editar edificios
    Route::get('Edificio/{edificio}/edit', [EdificiosController::class, 'edit'])->name('edificios.edit');
    Route::put('Edificio/{edificio}', [EdificiosController::class, 'update'])->name('edificios.update');
    // Para eliminar
    Route::delete('Edificio/{edificio}', [EdificiosController::class, 'destroy'])->name('edificios.destroy');



    //USUARIOS
    Route::get('Usuarios', [usersController::class, 'index'])->name('users.index');
    Route::get('Usuarios/create', [usersController::class, 'create'])->name('users.create');
    Route::post('Usuarios', [usersController::class, 'store'])->name('users.store');
    Route::get('Usuarios/{user}/edit', [usersController::class, 'edit'])->name('users.edit');
    Route::put('Usuarios/{user}', [usersController::class, 'update'])->name('users.update');
    Route::delete('Usuarios/{user}', [usersController::class, 'destroy'])->name('users.destroy');





    //PERFILES/ROLES
    Route::get('rolesperfil', [rolesController::class, 'index'])->name('rolesperfil.index');
    Route::get('rolesperfil/create', [rolesController::class, 'create'])->name('rolesperfil.create');
    Route::post('roles', [rolesController::class, 'store'])->name('roles.store');
    Route::post('usuarioRoles', [rolesController::class, 'storeUsuarioRole'])->name('usuarioRoles.store');

    // Rutas de Unidades (nuevo módulo)
    Route::get('Unidades', [unidadesController::class, 'index'])->name('unidades.index');
    Route::get('Unidades/create', [unidadesController::class, 'create'])->name('unidades.create');
    Route::post('Unidades', [unidadesController::class, 'store'])->name('unidades.store');
    Route::get('Unidades/{unidad}/edit', [unidadesController::class, 'edit'])->name('unidades.edit');
    Route::put('Unidades/{unidad}', [unidadesController::class, 'update'])->name('unidades.update');
    Route::delete('Unidades/{unidad}', [unidadesController::class, 'destroy'])->name('unidades.destroy');


    // Rutas de ProrrateoValores
    Route::get('Prorrateos', [ProrrateoValoresController::class, 'index'])->name('prorrateos.index');
    Route::get('Prorrateos/create', [ProrrateoValoresController::class, 'create'])->name('prorrateos.create');
    Route::post('Prorrateos', [ProrrateoValoresController::class, 'store'])->name('prorrateos.store');
    Route::get('Prorrateos/{prorrateoValor}/edit', [ProrrateoValoresController::class, 'edit'])->name('prorrateos.edit');
    Route::put('Prorrateos/{prorrateoValor}', [ProrrateoValoresController::class, 'update'])->name('prorrateos.update');
    Route::delete('Prorrateos/{prorrateoValor}', [ProrrateoValoresController::class, 'destroy'])->name('prorrateos.destroy');

    // TipoProrrateo
    Route::get('TipoProrrateo', [tipo_prorrateoController::class, 'index'])->name('tipos.index');
    Route::get('TipoProrrateo/create', [tipo_prorrateoController::class, 'create'])->name('tipos.create');
    Route::post('TipoProrrateo', [tipo_prorrateoController::class, 'store'])->name('tipos.store');
    Route::get('TipoProrrateo/{tipo}/edit', [tipo_prorrateoController::class, 'edit'])->name('tipos.edit');
    Route::put('TipoProrrateo/{tipo}', [tipo_prorrateoController::class, 'update'])->name('tipos.update');
    Route::delete('TipoProrrateo/{tipo}', [tipo_prorrateoController::class, 'destroy'])->name('tipos.destroy');

    // TipoProrrateoCondominio
    Route::get('TipoProrrateoCondominio', [TipoProrrateoCondominioController::class, 'index'])->name('tipo_prorrateo_condominio.index');
    Route::get('TipoProrrateoCondominio/create', [TipoProrrateoCondominioController::class, 'create'])->name('tipo_prorrateo_condominio.create');
    Route::post('TipoProrrateoCondominio', [TipoProrrateoCondominioController::class, 'store'])->name('tipo_prorrateo_condominio.store');
    Route::get('TipoProrrateoCondominio/{registro}/edit', [TipoProrrateoCondominioController::class, 'edit'])->name('tipo_prorrateo_condominio.edit');
    Route::put('TipoProrrateoCondominio/{registro}', [TipoProrrateoCondominioController::class, 'update'])->name('tipo_prorrateo_condominio.update');
    Route::delete('TipoProrrateoCondominio/{registro}', [TipoProrrateoCondominioController::class, 'destroy'])->name('tipo_prorrateo_condominio.destroy');


    // Rutas para Categorías de Gasto Común
    Route::get('CategoriaGastoComun', [CategoriaGastoComunController::class, 'index'])->name('categoria_gasto_comun.index');
    Route::get('CategoriaGastoComun/create', [CategoriaGastoComunController::class, 'create'])->name('categoria_gasto_comun.create');
    Route::post('CategoriaGastoComun', [CategoriaGastoComunController::class, 'store'])->name('categoria_gasto_comun.store');
    Route::get('CategoriaGastoComun/{categoria}/edit', [CategoriaGastoComunController::class, 'edit'])->name('categoria_gasto_comun.edit');
    Route::put('CategoriaGastoComun/{categoria}', [CategoriaGastoComunController::class, 'update'])->name('categoria_gasto_comun.update');
    Route::delete('CategoriaGastoComun/{categoria}', [CategoriaGastoComunController::class, 'destroy'])->name('categoria_gasto_comun.destroy');

    // Rutas para Tipo de Gasto Común (ya definidas anteriormente)
    Route::get('TipoGastoComun', [TipoGastoComunController::class, 'index'])->name('tipo_gasto_comun.index');
    Route::get('TipoGastoComun/create', [TipoGastoComunController::class, 'create'])->name('tipo_gasto_comun.create');
    Route::post('TipoGastoComun', [TipoGastoComunController::class, 'store'])->name('tipo_gasto_comun.store');
    Route::get('TipoGastoComun/{tipo}/edit', [TipoGastoComunController::class, 'edit'])->name('tipo_gasto_comun.edit');
    Route::put('TipoGastoComun/{tipo}', [TipoGastoComunController::class, 'update'])->name('tipo_gasto_comun.update');
    Route::delete('TipoGastoComun/{tipo}', [TipoGastoComunController::class, 'destroy'])->name('tipo_gasto_comun.destroy');

    // Rutas Gastos Comunes
    Route::get('GastosComunes', [gastos_comunesController::class, 'index'])->name('gastos_comunes.index');
    Route::get('GastosComunes/create', [gastos_comunesController::class, 'create'])->name('gastos_comunes.create');
    Route::post('GastosComunes', [gastos_comunesController::class, 'store'])->name('gastos_comunes.store');
    Route::get('GastosComunes/{gasto}/edit', [gastos_comunesController::class, 'edit'])->name('gastos_comunes.edit');
    Route::put('GastosComunes/{gasto}', [gastos_comunesController::class, 'update'])->name('gastos_comunes.update');
    Route::delete('GastosComunes/{gasto}', [gastos_comunesController::class, 'destroy'])->name('gastos_comunes.destroy');



    Route::post('GastosComunes/{gasto}/calculateDistribution', [gastos_comunesController::class, 'calculateDistribution'])->name('gastos_comunes.calculateDistribution');
    Route::post('GastosComunes/{gasto}/validateDistribution', [gastos_comunesController::class, 'validateDistribution'])->name('gastos_comunes.validateDistribution');
    // ========================================
    // NUEVAS RUTAS: Manejo de CONSUMOS
    // ========================================
    // 1) Vista previa de mediciones pendientes (paginada)
    Route::get('GastosComunes/{gasto}/previewConsumption', [gastos_comunesController::class, 'previewConsumption'])->name('gastos_comunes.preview_consumption');
    // 2) Convertir esas mediciones en Detalles del Gasto
    Route::post('GastosComunes/{gasto}/distributeConsumption', [gastos_comunesController::class, 'distributeConsumption'])->name('gastos_comunes.distribute_consumption');
    // ========================================
    // NUEVAS RUTAS: Distribución FINAL
    // ========================================
    // 3) Previsualizar cómo quedaría la distribución final
    Route::get('GastosComunes/{gasto}/finalDistributionPreview', [gastos_comunesController::class, 'finalDistributionPreview'])->name('gastos_comunes.final_distribution_preview');
    // 4) Confirmar y ejecutar definitivamente la distribución
    Route::post('GastosComunes/{gasto}/confirmFinalDistribution', [gastos_comunesController::class, 'confirmFinalDistribution'])->name('gastos_comunes.confirm_final_distribution');





    //Mismo gasto comun pero para detalle para separar la logica:
    Route::post('detalle_gasto_comun/storeMultiple', [detalle_gasto_comunController::class, 'storeMultiple'])->name('detalle_gasto_comun.storeMultiple');
    Route::put('detalle_gasto_comun/{detalle}', [detalle_gasto_comunController::class, 'update'])->name('detalle_gasto_comun.update');
    Route::delete('detalle_gasto_comun/{detalle}', [detalle_gasto_comunController::class, 'destroy'])->name('detalle_gasto_comun.destroy');

    //hasta aqui esot es para guardar muctiples

    //detallegasto comun
    Route::get('DetalleGasto', [detalle_gasto_comunController::class, 'index'])->name('detalle_gasto_comun.index');
    Route::get('DetalleGasto/create', [detalle_gasto_comunController::class, 'create'])->name('detalle_gasto_comun.create');
    Route::post('DetalleGasto', [detalle_gasto_comunController::class, 'store'])->name('detalle_gasto_comun.store');
    Route::get('DetalleGasto/{detalle}/edit', [detalle_gasto_comunController::class, 'edit'])->name('detalle_gasto_comun.edit');
    Route::post('DetalleGasto/{detalle}', [detalle_gasto_comunController::class, 'update'])->name('detalle_gasto_comun.update_post');


    Route::delete('DetalleGasto/{detalle}', [detalle_gasto_comunController::class, 'destroy'])->name('detalle_gasto_comun.destroy_alt');


    // Rutas para Distribución de Gastos
    Route::get('DistribucionGasto', [DistribucionGastoController::class, 'index'])->name('distribucion_gasto.index');
    Route::get('DistribucionGasto/create', [DistribucionGastoController::class, 'create'])->name('distribucion_gasto.create');
    Route::post('DistribucionGasto', [DistribucionGastoController::class, 'store'])->name('distribucion_gasto.store');
    Route::get('DistribucionGasto/{id}/edit', [DistribucionGastoController::class, 'edit'])->name('distribucion_gasto.edit');

    // Vista de pagos del usuario
    Route::get('/pagos', [pagosController::class, 'index'])->name('pagos.index');
    // Ruta para el dashboard (gráficos y tabla)

    Route::get('/pagos/pendientes', [pagosController::class, 'pendientes'])->name('pagos.pendientes');

    // Form para crear pago (subir comprobante)--para el tema de pagos de usuario arrendador
    Route::get('/pagos/create', [pagosController::class, 'create'])->name('pagos.create');
    Route::post('/pagos', [pagosController::class, 'store'])->name('pagos.store');
    Route::get('/pagos/{id}/comprobante', [pagosController::class, 'downloadReceipt'])
        ->name('pagos.downloadReceipt')
        ->middleware('auth');

    Route::post('/pagos/{idPago}', [pagosController::class, 'update'])->name('pagos.update');




    // Ruta para obtener todos los gastos comunes (para el dashboard)
    Route::get('gastos-todos', [pagosController::class, 'dashboardGastos'])->name('dashboard.gastos');
    // Ruta para obtener datos históricos de gastos
    Route::get('historical-gastos', [pagosController::class, 'historicalGastos'])->name('dashboard.historical');
    /////


    // La ruta principal del Dashboard se dirige al método index del AdminpagosController.
    Route::get('/', [AdminpagosController::class, 'index'])->name('dashboard');
    // Ruta para validar un pago (la acción "Validar")
    Route::post('/admin/pagos/validate', [AdminpagosController::class, 'validatePago'])
        ->name('admin.pagos.validate');



    // Rutas del Propietario
    Route::prefix('propietario')->group(function () {
        Route::get('/', [\App\Http\Controllers\PropietarioController::class, 'index'])
            ->name('propietario.index');

        Route::get('/arrendatario-chart', [\App\Http\Controllers\PropietarioController::class, 'arrendatarioChart'])
            ->name('propietario.arrendatarioChart');

        // (Opcional) Para ver pagos realizados
        Route::get('/pagos-realizados', [\App\Http\Controllers\PropietarioController::class, 'pagosRealizados'])
            ->name('propietario.pagosRealizados');
    });

    //vigilancia
    Route::get('vigilancia', [VigilanciaController::class, 'index'])
        ->name('vigilancia.index');


    //para configuraciones de periodos
    Route::get('Periodos', [ConfiguracionPeriodosController::class, 'index'])->name('periodos.index');
    Route::get('Periodos/create', [ConfiguracionPeriodosController::class, 'create'])->name('periodos.create');
    Route::post('Periodos', [ConfiguracionPeriodosController::class, 'store'])->name('periodos.store');
    Route::get('Periodos/{periodo}/edit', [ConfiguracionPeriodosController::class, 'edit'])->name('periodos.edit');
    Route::put('Periodos/{periodo}', [ConfiguracionPeriodosController::class, 'update'])->name('periodos.update');
    Route::delete('Periodos/{periodo}', [ConfiguracionPeriodosController::class, 'destroy'])->name('periodos.destroy');


    // Rutas de Extenciones
    // Rutas para el módulo de Extenciones
    Route::get('extenciones', [ExtencionesController::class, 'index'])->name('extenciones.index');
    Route::get('extenciones/create', [ExtencionesController::class, 'create'])->name('extenciones.create');
    Route::post('extenciones', [ExtencionesController::class, 'store'])->name('extenciones.store');
    Route::get('extenciones/{extencion}/edit', [ExtencionesController::class, 'edit'])->name('extenciones.edit');
    Route::put('extenciones/{extencion}', [ExtencionesController::class, 'update'])->name('extenciones.update');
    Route::delete('extenciones/{extencion}', [ExtencionesController::class, 'destroy'])->name('extenciones.destroy');



    // Rutas para Mediciones de Consumo
    Route::get('Mediciones', [MedicionConsumoController::class, 'index'])->name('mediciones.index');
    Route::get('Mediciones/create', [MedicionConsumoController::class, 'create'])->name('mediciones.create');
    Route::post('Mediciones', [MedicionConsumoController::class, 'store'])->name('mediciones.store');
    Route::get('Mediciones/{medicione}/edit', [MedicionConsumoController::class, 'edit'])->name('mediciones.edit');
    Route::put('Mediciones/{medicione}', [MedicionConsumoController::class, 'update'])->name('mediciones.update');
    Route::delete('Mediciones/{medicione}', [MedicionConsumoController::class, 'destroy'])->name('mediciones.destroy');


    // Rutas para Configuración de Montos de Servicios (ConfigConsumoController)
    // ========================================================
    Route::get('ConfigServicios', [ConfigConsumoController::class, 'index'])->name('config_servicios.index');
    Route::post('ConfigServicios', [ConfigConsumoController::class, 'store'])->name('config_servicios.store');
    Route::put('ConfigServicios/{id}', [ConfigConsumoController::class, 'update'])->name('config_servicios.update');
    Route::delete('ConfigServicios/{id}', [ConfigConsumoController::class, 'destroy'])->name('config_servicios.destroy');


    //confpago
    Route::get('/configuracion-pagos', [ConfPagoController::class, 'index'])->name('DatosAdministrador.index');
    Route::post('/configuracion-pagos', [ConfPagoController::class, 'store'])->name('DatosAdministrador.store');
    Route::post('/configuracion-pagos/{id}/activar', [ConfPagoController::class, 'toggle'])->name('DatosAdministrador.toggle');
    Route::delete('/configuracion-pagos/{id}', [ConfPagoController::class, 'destroy'])->name('DatosAdministrador.destroy');
    Route::post('/configuracion-pagos/{id}', [ConfPagoController::class, 'update'])->name('DatosAdministrador.update');

    //REPORTES
    Route::get('reporte-ingresos', [ReporteIngresosController::class, 'index'])->name('reporte.ingresos');
    Route::get('reporte-ingresos/export/excel', [ReporteIngresosController::class, 'exportExcel'])->name('reporte.ingresos.export.excel');
    Route::get('reporte-ingresos/export/csv', [ReporteIngresosController::class, 'exportCsv'])->name('reporte.ingresos.export.csv');
    //conf  mora
    Route::get('/conf-mora', [ConfMoraController::class, 'index'])->name('ConfMora.index');
    Route::post('/conf-mora', [ConfMoraController::class, 'store'])->name('ConfMora.store');
    Route::post('/conf-mora/{id}', [ConfMoraController::class, 'update'])->name('ConfMora.update');
    Route::delete('/conf-mora/{id}', [ConfMoraController::class, 'destroy'])->name('ConfMora.destroy');
});




require __DIR__ . '/auth.php';
