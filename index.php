<?php
// Configuración de la base de datos
$host = 'dpg-cpvugojv2p9s73c2e010-a.oregon-postgres.render.com';
$db = 'umd_tools';
$user = 'administrador';
$password = 'rvB020XqyrTx4FJRCN8Nm8Bo9v0BySWr';

try {
    // Crear una nueva conexión PDO
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("No se pudo conectar a la base de datos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Endpoint para añadir usuario
    if ($_SERVER['REQUEST_URI'] === '/user/add') {
        // Obtener los datos enviados en el cuerpo de la solicitud
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar los datos
        if (
            isset($data['nombres']) &&
            isset($data['apellidos']) &&
            isset($data['tipo_identificacion']) &&
            isset($data['numero_identificacion']) &&
            isset($data['email_personal']) &&
            isset($data['email_institucional']) &&
            isset($data['celular']) &&
            isset($data['direccion_residencia']) &&
            isset($data['departamento']) &&
            isset($data['ciudad']) &&
            isset($data['facultad']) &&
            isset($data['programa']) &&
            isset($data['password'])
        ) {
            try {
                // Preparar la consulta SQL
                $stmt = $pdo->prepare('
                    INSERT INTO usuarios (
                        nombres, apellidos, tipo_identificacion, numero_identificacion,
                        email_personal, email_institucional, celular, direccion_residencia,
                        departamento, ciudad, facultad, programa, password
                    ) VALUES (
                        :nombres, :apellidos, :tipo_identificacion, :numero_identificacion,
                        :email_personal, :email_institucional, :celular, :direccion_residencia,
                        :departamento, :ciudad, :facultad, :programa, :password
                    )
                ');

                // Ejecutar la consulta con los datos
                $stmt->execute([
                    ':nombres' => $data['nombres'],
                    ':apellidos' => $data['apellidos'],
                    ':tipo_identificacion' => $data['tipo_identificacion'],
                    ':numero_identificacion' => $data['numero_identificacion'],
                    ':email_personal' => $data['email_personal'],
                    ':email_institucional' => $data['email_institucional'],
                    ':celular' => $data['celular'],
                    ':direccion_residencia' => $data['direccion_residencia'],
                    ':departamento' => $data['departamento'],
                    ':ciudad' => $data['ciudad'],
                    ':facultad' => $data['facultad'],
                    ':programa' => $data['programa'],
                    ':password' => password_hash($data['password'], PASSWORD_DEFAULT)
                ]);

                // Responder con éxito
                http_response_code(201);
                echo json_encode(['message' => 'Usuario creado con éxito']);
            } catch (PDOException $e) {
                // Manejar errores de la base de datos
                http_response_code(500);
                echo json_encode(['message' => 'Error al insertar el usuario', 'error' => $e->getMessage()]);
            }
        } else {
            // Responder con error si faltan datos
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos']);
        }
    } elseif ($_SERVER['REQUEST_URI'] === '/contacto/add') {
        // Obtener los datos enviados en el cuerpo de la solicitud
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar los datos
        if (
            isset($data['nombre']) &&
            isset($data['email']) &&
            isset($data['asunto']) &&
            isset($data['mensaje'])
        ) {
            try {
                // Preparar la consulta SQL para la tabla contactos
                $stmt = $pdo->prepare('
                    INSERT INTO contactos (nombre, email, asunto, mensaje)
                    VALUES (:nombre, :email, :asunto, :mensaje)
                ');

                // Ejecutar la consulta con los datos
                $stmt->execute([
                    ':nombre' => $data['nombre'],
                    ':email' => $data['email'],
                    ':asunto' => $data['asunto'],
                    ':mensaje' => $data['mensaje']
                ]);

                // Responder con éxito
                http_response_code(201);
                echo json_encode(['message' => 'Contacto agregado con éxito']);
            } catch (PDOException $e) {
                // Manejar errores de la base de datos
                http_response_code(500);
                echo json_encode(['message' => 'Error al insertar el contacto', 'error' => $e->getMessage()]);
            }
        } else {
            // Responder con error si faltan datos
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos']);
        }
    } else {
        // Responder con error si la ruta no es /user/add ni /contacto/add
        http_response_code(404);
        echo json_encode(['message' => 'Ruta no encontrada']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_SERVER['REQUEST_URI'] === '/user/login') {
        // Obtener los datos enviados en los parámetros de la solicitud
        if (isset($_GET['email_institucional']) && isset($_GET['password'])) {
            $email = $_GET['email_institucional'];
            $password = $_GET['password'];

            try {
                // Preparar la consulta SQL para buscar el usuario
                $stmt = $pdo->prepare('SELECT password FROM usuarios WHERE email_institucional = :email');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Responder con éxito si la contraseña es correcta
                    http_response_code(200);
                    echo json_encode(['message' => 'true']);
                } else {
                    // Responder con error si la contraseña es incorrecta o el usuario no existe
                    http_response_code(200);
                    echo json_encode(['message' => 'false']);
                }
            } catch (PDOException $e) {
                // Manejar errores de la base de datos
                http_response_code(500);
                echo json_encode(['message' => 'Error al verificar el usuario', 'error' => $e->getMessage()]);
            }
        } else {
            // Responder con error si faltan datos
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos']);
        }
    } elseif ($_SERVER['REQUEST_URI'] === '/') {
        // Responder con un mensaje en la raíz
        http_response_code(200);
        echo "hola umd tools";
    } else {
        // Responder con error si la ruta no es /user/login ni /
        http_response_code(404);
        echo json_encode(['message' => 'Ruta no encontrada']);
    }
} else {
    // Responder con error si el método no es POST ni GET en los endpoints esperados
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}
?>
