<?php
/**
 * Ajax Handler para Game Jam
 * 
 * Este archivo procesa todas las solicitudes AJAX para la Game Jam
 */

// Incluir el archivo con las funciones de base de datos
require_once 'db_functions.php';

// Verificar si existe la acción
if (!isset($_POST['accion'])) {
    enviarRespuesta(false, 'No se especificó ninguna acción');
}

// Procesar la acción solicitada
switch ($_POST['accion']) {
    case 'registrar_equipo':
        // Procesar el formulario de registro
        $resultado = procesarFormulario();
        
        if (is_array($resultado) && isset($resultado['success']) && $resultado['success']) {
            enviarRespuesta(true, $resultado['message'], [
                'equipo_id' => $resultado['equipo_id'], 
                'total_equipos' => contarEquipos() + 32 // Base ficticia + registros reales
            ]);
        } else {
            enviarRespuesta(false, $resultado);
        }
        break;
        
    case 'obtener_equipos':
        // Obtener listado de equipos para la tabla
        $html_tabla = renderizarTablaEquipos();
        
        enviarRespuesta(true, 'Equipos obtenidos correctamente', [
            'html_tabla' => $html_tabla,
            'total_equipos' => contarEquipos() + 32 // Base ficticia + registros reales
        ]);
        break;
        
    case 'eliminar_equipo':
        // Verificar ID del equipo
        if (!isset($_POST['equipo_id']) || !is_numeric($_POST['equipo_id'])) {
            enviarRespuesta(false, 'ID de equipo no válido');
        }
        
        $equipo_id = intval($_POST['equipo_id']);
        
        // Eliminar equipo
        $resultado = eliminarEquipo($equipo_id);
        
        if ($resultado) {
            enviarRespuesta(true, 'Equipo eliminado correctamente', [
                'total_equipos' => contarEquipos() + 32 // Base ficticia + registros reales
            ]);
        } else {
            enviarRespuesta(false, 'Error al eliminar el equipo');
        }
        break;
        
    case 'ver_equipo':
        // Verificar ID del equipo
        if (!isset($_POST['equipo_id']) || !is_numeric($_POST['equipo_id'])) {
            enviarRespuesta(false, 'ID de equipo no válido');
        }
        
        $equipo_id = intval($_POST['equipo_id']);
        
        // Obtener detalles del equipo
        $equipo = obtenerDetallesEquipo($equipo_id);
        
        if ($equipo) {
            enviarRespuesta(true, 'Detalles del equipo obtenidos', [
                'equipo' => $equipo
            ]);
        } else {
            enviarRespuesta(false, 'No se encontró el equipo');
        }
        break;
        
    case 'contar_equipos':
        // Obtener total de equipos
        $total = contarEquipos();
        
        if ($total !== false) {
            enviarRespuesta(true, 'Total de equipos obtenido', [
                'total_equipos' => $total + 32 // Base ficticia + registros reales
            ]);
        } else {
            enviarRespuesta(false, 'Error al contar equipos');
        }
        break;
        
    default:
        enviarRespuesta(false, 'Acción no reconocida');
}

/**
 * Envía una respuesta JSON al cliente
 * 
 * @param bool $exito Si la operación fue exitosa
 * @param string $mensaje Mensaje descriptivo
 * @param array $datos Datos adicionales para incluir en la respuesta
 */
function enviarRespuesta($exito, $mensaje, $datos = []) {
    $respuesta = [
        'exito' => $exito,
        'mensaje' => $mensaje
    ];
    
    if (!empty($datos)) {
        $respuesta['datos'] = $datos;
    }
    
    header('Content-Type: application/json');
    echo json_encode($respuesta);
    exit;
}
?>