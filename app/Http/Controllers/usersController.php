<?php

namespace App\Http\Controllers;

use App\Models\Condominio;
use App\Models\Role;
use App\Models\Unidade;
use App\Models\Usuario;
use App\Models\TipoProrrateo;
use App\Models\Extencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Muestra el listado de usuarios con búsqueda.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Usuario::query();

        // Cargar las relaciones necesarias
        //$query->with(['roles', 'tipoProrrateo.prorrateoValores', 'unidad.edificio', 'extenciones']);

        // Cargar las relaciones necesarias sin la relación "edificio" en extenciones
        $query->with([
            'roles',
            'tipoProrrateo.prorrateoValores',
            'unidad.edificio',
            'extenciones',                // Carga solo los datos de extenciones
            'extenciones.serviciosExtras' // Si necesitas los servicios extras
        ]);


        // Si el usuario actual tiene un condominio asignado, filtramos
        if (Auth::user()->id_condominio) {
            $query->where('id_condominio', Auth::user()->id_condominio);
        }

        // Filtro de búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        // Orden y paginación
        $users = $query->orderBy('id', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('Usuarios/Index', [
            'users'   => $users,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        // Si el usuario logueado NO tiene condominio (es SuperAdmin), traemos todos
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        } else {
            $condominios = [];
        }

        // Obtenemos el rol del usuario actual
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        $currentRole = $user->roles()->first();
        $user = Auth::user();

        // Si es Administrador (id_rol = 2), excluimos el rol SuperAdmin (id_rol = 1)
        if ($currentRole && $currentRole->id_rol == 2) {
            $roles = Role::where('id_rol', '!=', 1)->orderBy('nombre')->get();
        } else {
            $roles = Role::orderBy('nombre')->get();
        }

        // Unidades (filtradas por condominio si aplica)
        if (Auth::user()->id_condominio) {
            $unidades = Unidade::with('edificio')
                ->where('id_condominio', Auth::user()->id_condominio)
                ->orderBy('nombre_unidad')
                ->get();
        } else {
            $unidades = Unidade::with('edificio')
                ->orderBy('nombre_unidad')
                ->get();
        }

        // Tipos de prorrateo (con sus detalles)
        $tiposProrrateo = TipoProrrateo::with('prorrateoValores')
            ->orderBy('descripcion')
            ->get();

        // Tipos de documento
        $documentTypes = [
            'DNI',
            'CETP',
            'RUC',
            'Pasaporte',
            'Otros'
        ];

        // Extenciones disponibles (filtradas por condominio si aplica)
        $extenciones = Extencion::with(['edificio', 'serviciosExtras'])
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->get();

        return Inertia::render('Usuarios/Create', [
            'condominios'    => $condominios,
            'roles'          => $roles,
            'unidades'       => $unidades,
            'tiposProrrateo' => $tiposProrrateo,
            'documentTypes'  => $documentTypes,
            'extenciones'    => $extenciones,
        ]);
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        // Reglas de validación
        $rules = [
            'name'              => 'required|string|max:255',
            'apellidos'         => 'nullable|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email',
            'telefono'          => 'nullable|string|max:20',
            'password'          => 'required|string|min:6',
            'estado'            => 'nullable|boolean',
            'id_condominio'     => 'required|exists:condominios,id_condominio',
            'id_rol'            => 'required|exists:roles,id_rol',
            // NUEVOS CAMPOS
            'tipo_documento'    => 'nullable|string|max:50',
            'numero_documento'  => 'nullable|string|max:50',
            // Para extenciones
            'tiene_extencion'   => 'nullable|boolean',
            'extenciones'       => 'array',
            'extenciones.*'     => 'integer|exists:extenciones,id_extencion'
        ];

        // Si el rol es arrendatario (3) o propietario (4), se requiere unidad
        if (in_array($request->input('id_rol'), [3, 4])) {
            $rules['id_unidad'] = 'required|exists:unidades,id_unidad';
        } else {
            $rules['id_unidad'] = 'nullable|exists:unidades,id_unidad';
        }

        // Valida
        $validated = $request->validate($rules);

        // Forzar id_condominio si el usuario actual tiene uno
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        // Estado: true/false → 1/0
        $validated['estado'] = $request->boolean('estado') ? 1 : 0;

        // Creamos el usuario
        $user = Usuario::create([
            'name'              => $validated['name'],
            'apellidos'         => $validated['apellidos'] ?? null,
            'email'             => $validated['email'],
            'telefono'          => $validated['telefono'] ?? null,
            'rut'               => $validated['rut'] ?? null,
            'password'          => Hash::make($validated['password']),
            'estado'            => $validated['estado'],
            'id_condominio'     => $validated['id_condominio'],
            'id_unidad'         => $validated['id_unidad'] ?? null,
            'id_tipo_prorrateo' => $validated['id_tipo_prorrateo'] ?? null,
            'tipo_documento'    => $validated['tipo_documento'] ?? null,
            'numero_documento'  => $validated['numero_documento'] ?? null,
            // Guardamos el valor de tiene_extencion (0/1)
            'tiene_extencion'   => $request->boolean('tiene_extencion') ? 1 : 0,
        ]);

        // Asignar rol al usuario
        $user->roles()->attach($validated['id_rol']);

        // Si el usuario marcó “tiene_extencion” y envió extenciones, sincronizar pivot
        if ($request->boolean('tiene_extencion') && $request->filled('extenciones')) {
            $user->extenciones()->sync($validated['extenciones']);
        } else {
            // Si no, asegurarnos de que no tenga extenciones en pivot
            $user->extenciones()->sync([]);
        }

        return redirect()->route('users.index')
            ->with('success', '¡Usuario creado exitosamente!');
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(Usuario $user)
    {
        // Cargar relaciones necesarias del usuario (roles, unidad->edificio, extenciones->edificio+servicios)
        $user->load([
            'roles',
            'unidad.edificio',
            'extenciones.edificio',
            'extenciones.serviciosExtras',
            'tipoProrrateo.prorrateoValores'
        ]);

        // Si es SuperAdmin, mostramos condominios; si no, array vacío
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        } else {
            $condominios = [];
        }

        // Roles (en tu ejemplo no estás excluyendo nada en edit, ajusta si deseas)
        $roles = Role::orderBy('nombre')->get();

        // Unidades (filtradas por condominio si aplica)
        if (Auth::user()->id_condominio) {
            $unidades = Unidade::with('edificio')
                ->where('id_condominio', Auth::user()->id_condominio)
                ->orderBy('nombre_unidad')
                ->get();
        } else {
            $unidades = Unidade::with('edificio')
                ->orderBy('nombre_unidad')
                ->get();
        }

        // Tipos de prorrateo
        $tiposProrrateo = TipoProrrateo::with('prorrateoValores')
            ->orderBy('descripcion')
            ->get();

        // DocumentTypes
        $documentTypes = [
            'DNI',
            'CETP',
            'RUC',
            'Pasaporte',
            'Otros'
        ];

        // Extenciones disponibles
        $extenciones = Extencion::with(['edificio', 'serviciosExtras'])
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->get();

        return Inertia::render('Usuarios/Edit', [
            'user'           => $user,
            'condominios'    => $condominios,
            'roles'          => $roles,
            'unidades'       => $unidades,
            'tiposProrrateo' => $tiposProrrateo,
            'documentTypes'  => $documentTypes,
            'extenciones'    => $extenciones,
        ]);
    }

    /**
     * Actualiza la información de un usuario.
     */
    public function update(Request $request, Usuario $user)
    {
        $rules = [
            'name'             => 'required|string|max:255',
            'apellidos'        => 'nullable|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'telefono'         => 'nullable|string|max:20',
            'password'         => 'nullable|string|min:6',
            'estado'           => 'nullable|boolean',
            'id_condominio'    => 'required|exists:condominios,id_condominio',
            'id_rol'           => 'required|exists:roles,id_rol',
            // Nuevos campos
            'tipo_documento'   => 'nullable|string|max:50',
            'numero_documento' => 'nullable|string|max:50',
            // Extenciones
            'tiene_extencion'  => 'nullable|boolean',
            'extenciones'      => 'array',
            'extenciones.*'    => 'integer|exists:extenciones,id_extencion'
        ];

        // Si el rol es arrendatario (3) o propietario (4), requerimos unidad
        if (in_array($request->input('id_rol'), [3, 4])) {
            $rules['id_unidad'] = 'required|exists:unidades,id_unidad';
        } else {
            $rules['id_unidad'] = 'nullable|exists:unidades,id_unidad';
        }

        $validated = $request->validate($rules);

        // Forzar id_condominio si el usuario actual tiene uno
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        // Convertir estado a 1 o 0
        $validated['estado'] = $request->boolean('estado') ? 1 : 0;

        // Si la contraseña vino vacía, no se actualiza
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Actualizamos 'tiene_extencion' (0/1)
        $validated['tiene_extencion'] = $request->boolean('tiene_extencion') ? 1 : 0;

        // Actualizamos el usuario con los datos validados
        $user->update($validated);

        // Actualizamos el rol en la tabla pivot usuario_roles
        $user->roles()->sync([$validated['id_rol']]);

        // Sincronizar extenciones en la pivot
        if ($request->boolean('tiene_extencion') && $request->filled('extenciones')) {
            $user->extenciones()->sync($validated['extenciones']);
        } else {
            $user->extenciones()->sync([]);
        }

        return redirect()->route('users.index')
            ->with('success', '¡Usuario actualizado exitosamente!');
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(Usuario $user)
    {
        // Validar si tiene unidad, pagos, reservas, etc.
        if ($user->unidad) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene una Unidad asignada.');
        }

        if ($user->pagos()->exists() || $user->reservas()->exists() || $user->visitas()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene registros asociados (pagos, reservas o visitas).');
        }

        // Eliminar el usuario
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', '¡Usuario eliminado exitosamente!');
    }
}
