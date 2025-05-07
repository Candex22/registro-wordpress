<?php

function enqueue_styles_child_theme() {

	$parent_style = 'parent-style';
	$child_style  = 'child-style';

	wp_enqueue_style( $parent_style,
				get_template_directory_uri() . '/style.css' );

	wp_enqueue_style( $child_style,
				get_stylesheet_directory_uri() . '/style.css',
				array( $parent_style ),
				wp_get_theme()->get('Version')
				);
}
add_action( 'wp_enqueue_scripts', 'enqueue_styles_child_theme' );


/* Añade aquí tus funciones personalizadas */
function mi_tema_hijo_enqueue_scripts() {

    // (Si ya tienes la función para encolar estilos, puedes agregar esto dentro de esa misma función)

    // Enlaza tu archivo script.js
    wp_enqueue_script( 'mi-tema-script', // Un nombre único (handle) para identificar el script
        get_stylesheet_directory_uri() . '/script.js', // Ruta al archivo script.js en tu tema hijo
        array('jquery'), // Dependencias del script (ej: jQuery) - dejar vacío si no tiene dependencias
        filemtime( get_stylesheet_directory() . '/script.js' ), // Versión para control de caché (basado en la fecha de modificación)
        true // Cargar el script en el footer (recomendado para mejorar el rendimiento)
    );

    // Si tienes otro archivo JS específico (ej: mi-script-pagina.js)
    /*
    wp_enqueue_script( 'mi-script-pagina',
        get_stylesheet_directory_uri() . '/mi-script-pagina.js',
        array(), // Sin dependencias en este ejemplo
        filemtime( get_stylesheet_directory() . '/mi-script-pagina.js' ),
        true
    );
    */
}
add_action( 'wp_enqueue_scripts', 'mi_tema_hijo_enqueue_scripts' );
/**
 * Funciones para manejo de registros de la Game Jam en base de datos
 * 
 * Este archivo contiene todas las funciones necesarias para gestionar
 * los registros de equipos para la Game Jam "Pixel Forge 2025"
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wordpress');

/**
 * Establece una conexión con la base de datos
 * 
 * @return mysqli|false Objeto de conexión o false en caso de error
 */
function conectarDB() {
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if (mysqli_connect_errno()) {
        error_log("Error de conexión a la base de datos: " . mysqli_connect_error());
        return false;
    }
    
    // Establecer conjunto de caracteres
    mysqli_set_charset($conexion, "utf8mb4");
    
    return $conexion;
}

/**
 * Crea las tablas necesarias en la base de datos si no existen
 * 
 * @return bool True si las tablas se crearon correctamente, false en caso contrario
 */
function inicializarTablas() {
    $conexion = conectarDB();
    
    if (!$conexion) {
        return false;
    }
    
    // Tabla principal de equipos
    $sql_equipos = "CREATE TABLE IF NOT EXISTS equipos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_equipo VARCHAR(30) NOT NULL,
        nombre_lider VARCHAR(100) NOT NULL,
        tamano_equipo ENUM('solo', 'pequeno', 'mediano', 'grande') NOT NULL,
        email_contacto VARCHAR(100) NOT NULL,
        experiencia INT NOT NULL,
        descripcion_juego TEXT NOT NULL,
        ruta_logo VARCHAR(255) NOT NULL,
        comentarios_tecnicos TEXT,
        motor ENUM('unity', 'unreal', 'godot', 'gamemaker', 'custom', 'otro') NOT NULL,
        ruta_portfolio VARCHAR(255) NOT NULL,
        acepta_comunicaciones BOOLEAN DEFAULT 0,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Tabla para categorías (relación muchos a muchos)
    $sql_categorias = "CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        equipo_id INT NOT NULL,
        nombre_categoria VARCHAR(50) NOT NULL,
        FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE
    )";
    
    // Tabla para plataformas (relación muchos a muchos)
    $sql_plataformas = "CREATE TABLE IF NOT EXISTS plataformas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        equipo_id INT NOT NULL,
        nombre_plataforma VARCHAR(50) NOT NULL,
        FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE
    )";
    
    // Ejecutar consultas
    $resultado1 = mysqli_query($conexion, $sql_equipos);
    $resultado2 = mysqli_query($conexion, $sql_categorias);
    $resultado3 = mysqli_query($conexion, $sql_plataformas);
    
    mysqli_close($conexion);
    
    return ($resultado1 && $resultado2 && $resultado3);
}

/**
 * Guarda un nuevo registro de equipo en la base de datos
 * 
 * @param array $datos_equipo Datos del equipo a registrar
 * @return int|false ID del equipo registrado o false en caso de error
 */
function guardarRegistro($datos_equipo) {
    $conexion = conectarDB();
    
    if (!$conexion) {
        return false;
    }
    
    // Iniciar transacción
    mysqli_begin_transaction($conexion);
    
    try {
        // Preparar la consulta para la tabla equipos
        $stmt = mysqli_prepare($conexion, "INSERT INTO equipos (
            nombre_equipo, nombre_lider, tamano_equipo, email_contacto, 
            experiencia, descripcion_juego, ruta_logo, comentarios_tecnicos, 
            motor, ruta_portfolio, acepta_comunicaciones
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, 'ssssisssssi', 
            $datos_equipo['nombreEquipo'],
            $datos_equipo['nombreLider'],
            $datos_equipo['tamanoEquipo'],
            $datos_equipo['emailContacto'],
            $datos_equipo['experiencia'],
            $datos_equipo['descripcionJuego'],
            $datos_equipo['rutaLogo'],
            $datos_equipo['comentariosTecnicos'],
            $datos_equipo['motor'],
            $datos_equipo['rutaPortfolio'],
            $datos_equipo['aceptaComunicaciones']
        );
        
        // Ejecutar la consulta
        mysqli_stmt_execute($stmt);
        
        // Obtener el ID del equipo insertado
        $equipo_id = mysqli_insert_id($conexion);
        
        // Guardar categorías relacionadas
        if (!empty($datos_equipo['categorias'])) {
            foreach ($datos_equipo['categorias'] as $categoria) {
                $stmt_cat = mysqli_prepare($conexion, "INSERT INTO categorias (equipo_id, nombre_categoria) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt_cat, 'is', $equipo_id, $categoria);
                mysqli_stmt_execute($stmt_cat);
                mysqli_stmt_close($stmt_cat);
            }
        }
        
        // Guardar plataformas relacionadas
        if (!empty($datos_equipo['plataformas'])) {
            foreach ($datos_equipo['plataformas'] as $plataforma) {
                $stmt_plat = mysqli_prepare($conexion, "INSERT INTO plataformas (equipo_id, nombre_plataforma) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt_plat, 'is', $equipo_id, $plataforma);
                mysqli_stmt_execute($stmt_plat);
                mysqli_stmt_close($stmt_plat);
            }
        }
        
        // Confirmar la transacción
        mysqli_commit($conexion);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conexion);
        
        return $equipo_id;
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        mysqli_rollback($conexion);
        error_log("Error al guardar registro: " . $e->getMessage());
        return false;
    }
}

/**
 * Sube un archivo al servidor y devuelve la ruta
 * 
 * @param array $archivo Array $_FILES con información del archivo
 * @param string $directorio Directorio donde se guardará el archivo
 * @param string $prefijo Prefijo para el nombre del archivo
 * @return string|false Ruta del archivo subido o false en caso de error
 */
function subirArchivo($archivo, $directorio, $prefijo = '') {
    // Verificar si existe el directorio, si no, crearlo
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }
    
    // Generar nombre único para el archivo
    $nombre_archivo = $prefijo . '_' . uniqid() . '_' . basename($archivo['name']);
    $ruta_completa = $directorio . '/' . $nombre_archivo;
    
    // Mover el archivo subido
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return $ruta_completa;
    } else {
        error_log("Error al subir archivo: " . $archivo['name']);
        return false;
    }
}

/**
 * Obtiene la lista de equipos registrados
 * 
 * @param int $limite Número máximo de registros a devolver (0 = sin límite)
 * @param int $offset Desde qué registro empezar a contar
 * @return array|false Array con los datos de los equipos o false en caso de error
 */
function obtenerEquipos($limite = 0, $offset = 0) {
    $conexion = conectarDB();
    
    if (!$conexion) {
        return false;
    }
    
    $sql = "SELECT id, nombre_equipo, tamano_equipo, fecha_registro FROM equipos ORDER BY fecha_registro DESC";
    
    // Añadir límite si se especifica
    if ($limite > 0) {
        $sql .= " LIMIT ?, ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $offset, $limite);
    } else {
        $stmt = mysqli_prepare($conexion, $sql);
    }
    
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $equipos = [];
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // Obtener categorías del equipo
        $categorias = obtenerCategoriasPorEquipo($conexion, $fila['id']);
        
        // Obtener plataformas del equipo
        $plataformas = obtenerPlataformasPorEquipo($conexion, $fila['id']);
        
        $equipos[] = [
            'id' => $fila['id'],
            'nombreEquipo' => $fila['nombre_equipo'],
            'tamanoEquipo' => $fila['tamano_equipo'],
            'fechaRegistro' => $fila['fecha_registro'],
            'categorias' => $categorias,
            'plataformas' => $plataformas
        ];
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
    return $equipos;
}

/**
 * Obtiene las categorías asociadas a un equipo
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $equipo_id ID del equipo
 * @return array Array con las categorías
 */
function obtenerCategoriasPorEquipo($conexion, $equipo_id) {
    $stmt = mysqli_prepare($conexion, "SELECT nombre_categoria FROM categorias WHERE equipo_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $equipo_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $categorias = [];
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $categorias[] = $fila['nombre_categoria'];
    }
    
    mysqli_stmt_close($stmt);
    
    return $categorias;
}

/**
 * Obtiene las plataformas asociadas a un equipo
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $equipo_id ID del equipo
 * @return array Array con las plataformas
 */
function obtenerPlataformasPorEquipo($conexion, $equipo_id) {
    $stmt = mysqli_prepare($conexion, "SELECT nombre_plataforma FROM plataformas WHERE equipo_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $equipo_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $plataformas = [];
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $plataformas[] = $fila['nombre_plataforma'];
    }
    
    mysqli_stmt_close($stmt);
    
    return $plataformas;
}

/**
 * Obtiene los detalles completos de un equipo
 * 
 * @param int $equipo_id ID del equipo
 * @return array|false Array con los datos del equipo o false si no existe
 */
function obtenerDetallesEquipo($equipo_id) {
    $conexion = conectarDB();
    
    if (!$conexion) {
        return false;
    }
    
    $stmt = mysqli_prepare($conexion, "SELECT * FROM equipos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $equipo_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($resultado) == 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexion);
        return false;
    }
    
    $equipo = mysqli_fetch_assoc($resultado);
    
    // Obtener categorías relacionadas
    $equipo['categorias'] = obtenerCategoriasPorEquipo($conexion, $equipo_id);
    
    // Obtener plataformas relacionadas
    $equipo['plataformas'] = obtenerPlataformasPorEquipo($conexion, $equipo_id);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
    return $equipo;
}

/**
 * Elimina un equipo y todos sus datos relacionados
 * 
 * @param int $equipo_id ID del equipo a eliminar
 * @return bool True si se eliminó correctamente, false en caso contrario
 */
function eliminarEquipo($equipo_id) {
    $conexion = conectarDB();
    
    if (!$conexion) {
        return false;
    }
    
    // Primero obtenemos los datos del equipo para eliminar archivos físicos
    $equipo = obtenerDetallesEquipo($equipo_id);
    
    if ($equipo) {
        // Eliminar archivos físicos (logo y portfolio)
        if (file_exists($equipo['ruta_logo'])) {
            unlink($equipo['ruta_logo']);
        }
        
        if (file_exists($equipo['ruta_portfolio'])) {
            unlink($equipo['ruta_portfolio']);
        }
    }
    
    // Iniciar transacción
    mysqli_begin_transaction($conexion);
    
    try {
        // La eliminación de categorías y plataformas se hace automáticamente por las restricciones ON DELETE CASCADE
        $stmt = mysqli_prepare($conexion, "DELETE FROM equipos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $equipo_id);
        $resultado = mysqli_stmt_execute($stmt);
        
        mysqli_commit($conexion);
        mysqli_stmt_close($stmt);
        mysqli_close($conexion);
        
        return $resultado;
        
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        error_log("Error al eliminar equipo: " . $e->getMessage());
        return false;
    }
}

/**
 * Cuenta el número total de equipos registrados
 * 
 * @return int|false Número de equipos o false en caso de error
 */
function contarEquipos() {
    $conexion = conectarDB();
    
    if (!$conexion) {
        return false;
    }
    
    $stmt = mysqli_prepare($conexion, "SELECT COUNT(*) as total FROM equipos");
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
    return $fila['total'];
}

/**
 * Procesa los datos del formulario de registro
 * 
 * @return array|string Array con resultado exitoso o string con mensaje de error
 */
function procesarFormulario() {
    // Verificar si es una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return "Método no permitido";
    }
    
    try {
        // Validar campos obligatorios
        $campos_requeridos = [
            'nombreEquipo', 'nombreLider', 'tamanoEquipo', 'emailContacto', 
            'experiencia', 'descripcionJuego', 'motor', 'aceptarReglas'
        ];
        
        foreach ($campos_requeridos as $campo) {
            if (empty($_POST[$campo])) {
                return "Falta el campo obligatorio: $campo";
            }
        }
        
        // Validar que se ha seleccionado al menos una categoría
        if (!isset($_POST['categorias']) || !is_array($_POST['categorias']) || empty($_POST['categorias'])) {
            return "Debes seleccionar al menos una categoría";
        }
        
        // Validar que se ha seleccionado al menos una plataforma
        if (empty($_POST['plataformasSeleccionadas'])) {
            return "Debes seleccionar al menos una plataforma";
        }
        
        // Validar archivos
        if (!isset($_FILES['logoEquipo']) || $_FILES['logoEquipo']['error'] !== UPLOAD_ERR_OK) {
            return "Debes subir un logo para tu equipo";
        }
        
        if (!isset($_FILES['portfolioDemo']) || $_FILES['portfolioDemo']['error'] !== UPLOAD_ERR_OK) {
            return "Debes subir un portafolio o demo";
        }
        
        // Validar tipos de archivo
        $logo_tipo = mime_content_type($_FILES['logoEquipo']['tmp_name']);
        if (!in_array($logo_tipo, ['image/jpeg', 'image/jpg', 'image/png'])) {
            return "El logo debe ser una imagen JPG o PNG";
        }
        
        $portfolio_tipo = mime_content_type($_FILES['portfolioDemo']['tmp_name']);
        if ($portfolio_tipo !== 'application/zip') {
            return "El portafolio debe ser un archivo ZIP";
        }
        
        // Subir archivos
        $ruta_logo = subirArchivo($_FILES['logoEquipo'], 'uploads/logos', 'logo');
        $ruta_portfolio = subirArchivo($_FILES['portfolioDemo'], 'uploads/portfolios', 'portfolio');
        
        if (!$ruta_logo || !$ruta_portfolio) {
            return "Error al subir los archivos";
        }
        
        // Preparar datos para guardar
        $plataformas = explode(',', $_POST['plataformasSeleccionadas']);
        
        $datos_equipo = [
            'nombreEquipo' => htmlspecialchars($_POST['nombreEquipo']),
            'nombreLider' => htmlspecialchars($_POST['nombreLider']),
            'tamanoEquipo' => $_POST['tamanoEquipo'],
            'emailContacto' => filter_var($_POST['emailContacto'], FILTER_SANITIZE_EMAIL),
            'experiencia' => intval($_POST['experiencia']),
            'descripcionJuego' => htmlspecialchars($_POST['descripcionJuego']),
            'rutaLogo' => $ruta_logo,
            'comentariosTecnicos' => isset($_POST['comentariosTecnicos']) ? htmlspecialchars($_POST['comentariosTecnicos']) : '',
            'motor' => $_POST['motor'],
            'rutaPortfolio' => $ruta_portfolio,
            'categorias' => $_POST['categorias'],
            'plataformas' => $plataformas,
            'aceptaComunicaciones' => isset($_POST['aceptarComunicaciones']) ? 1 : 0
        ];
        
        // Guardar en la base de datos
        $equipo_id = guardarRegistro($datos_equipo);
        
        if (!$equipo_id) {
            return "Error al guardar el registro en la base de datos";
        }
        
        return [
            'success' => true,
            'message' => '¡Registro completado con éxito! Te esperamos en la Game Jam',
            'equipo_id' => $equipo_id
        ];
        
    } catch (Exception $e) {
        error_log("Error en procesarFormulario: " . $e->getMessage());
        return "Ha ocurrido un error inesperado. Inténtalo de nuevo más tarde.";
    }
}

/**
 * Renderiza la tabla de equipos registrados
 * 
 * @return string HTML de la tabla de equipos
 */
function renderizarTablaEquipos() {
    $equipos = obtenerEquipos();
    $html = '';
    
    if (!$equipos || count($equipos) === 0) {
        return '<tr><td colspan="5" style="text-align: center;">No hay equipos registrados aún</td></tr>';
    }
    
    // Mapeo de valores de tamaño de equipo a texto
    $tamanoEquipoMap = [
        'solo' => '1 persona',
        'pequeno' => '2-3 personas',
        'mediano' => '4-6 personas',
        'grande' => '7+ personas'
    ];
    
    foreach ($equipos as $equipo) {
        // Categorías como badges
        $categoriasBadges = array_map(function($cat) {
            return "<span class=\"badge\">{$cat}</span>";
        }, $equipo['categorias']);
        
        // Plataformas como badges
        $plataformasBadges = array_map(function($plat) {
            return "<span class=\"badge\">{$plat}</span>";
        }, $equipo['plataformas']);
        
        $html .= "
        <tr>
            <td>{$equipo['nombreEquipo']}</td>
            <td>" . implode(' ', $categoriasBadges) . "</td>
            <td>" . implode(' ', $plataformasBadges) . "</td>
            <td>{$tamanoEquipoMap[$equipo['tamanoEquipo']]}</td>
            <td>
                <button class=\"actions-btn view-btn\" data-id=\"{$equipo['id']}\"><i class=\"fas fa-eye\"></i></button>
                <button class=\"actions-btn delete-btn\" data-id=\"{$equipo['id']}\"><i class=\"fas fa-trash\"></i></button>
            </td>
        </tr>";
    }
    
    return $html;
}

// Inicializar tablas la primera vez
if (!function_exists('tablas_inicializadas')) {
    function tablas_inicializadas() {
        static $initialized = false;
        
        if (!$initialized) {
            inicializarTablas();
            $initialized = true;
        }
        
        return $initialized;
    }
    
    tablas_inicializadas();
}
?>
