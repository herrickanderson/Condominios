<?php

namespace App\Http\Controllers;

use App\Http\Requests\Condominios\StoreRequest;
use App\Http\Requests\Condominios\UpdateRequest;
use App\Models\Condominio;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use League\Flysystem\StorageAttributes;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\delete;

class condominiosController extends Controller
{
    /**
     * Display a listing of th  e resource.
     */
    public function index()
    {
        //$condominios = Condominio::where('id_condominio', Auth::user()->id)->get();//mostrar 1 que coincida cn el usuario
        //  $condominios = Condominio::all();


        // return Inertia::render('Condominios/Index', compact('condominios')); //puede ser asi

        //para paginacion
        // Se devuelven 5 condominios por página
        $condominios = Condominio::paginate(5);

        return Inertia::render('Condominios/Index', compact('condominios'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return Inertia::render('Condominios/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->except(['logo', 'firma']);

        // Obtiene la carpeta base definida en .env (produccion o testes)
        $baseFolder = env('S3_ENV_FOLDER', 'produccion');
        // Define la subcarpeta para condominios
        $folderPath = $baseFolder . '/condominios';

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            // Almacena en: {baseFolder}/condominios/logo
            $routeName = $file->store($folderPath . '/logo', 's3');
            $data['logo'] = $routeName;
        }

        if ($request->hasFile('firma')) {
            $fileFirma = $request->file('firma');
            // Almacena en: {baseFolder}/condominios/firma
            $firmaRoute = $fileFirma->store($folderPath . '/firma', 's3');
            $data['firma'] = $firmaRoute;
        }

        $data['id_condominio'] = Auth::user()->id;
        Condominio::create($data);

        return to_route('condominios.index');

    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Condominio $condominio)
    {
        return Inertia::render('Condominios/Edit', compact('condominio'));

        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Condominio $condominio)
    {
        $data = $request->except(['logo', 'firma']);
        $baseFolder = env('S3_ENV_FOLDER', 'produccion');
        $folderPath = $baseFolder . '/condominios';

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $routeName = $file->store($folderPath . '/logo', 's3');
            $data['logo'] = $routeName;
            if ($condominio->logo) {
                Storage::disk('s3')->delete($condominio->logo);
            }
        }

        if ($request->hasFile('firma')) {
            $fileFirma = $request->file('firma');
            $firmaRoute = $fileFirma->store($folderPath . '/firma', 's3');
            $data['firma'] = $firmaRoute;
            if ($condominio->firma) {
                Storage::disk('s3')->delete($condominio->firma);
            }
        }

        $condominio->update($data);
        return to_route('condominios.edit', $condominio);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Condominio $condominio)
    {
        //eliominamos imagenes logo y firma
        if ($condominio->logo) {
            Storage::disk('public')->delete(($condominio->logo));
        }
        if ($condominio->firma) {
            Storage::disk('public')->delete(($condominio->firma));
        }

        $condominio->delete();
        return to_route('condominios.index');
    }
}
