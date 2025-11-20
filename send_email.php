<?php
// Configuración de seguridad
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario
$data = json_decode(file_get_contents('php://input'), true);

// Si no hay JSON, intentar obtener desde POST normal
if (!$data) {
    $data = $_POST;
}

// Validar campos requeridos
$nombre = isset($data['name']) ? trim($data['name']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$asunto = isset($data['subject']) ? trim($data['subject']) : '';
$mensaje = isset($data['message']) ? trim($data['message']) : '';

// Validaciones
$errores = [];

if (empty($nombre)) {
    $errores[] = 'El nombre es requerido';
}

if (empty($email)) {
    $errores[] = 'El email es requerido';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no es válido';
}

if (empty($asunto)) {
    $errores[] = 'El asunto es requerido';
}

if (empty($mensaje)) {
    $errores[] = 'El mensaje es requerido';
}

// Si hay errores, retornar
if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Por favor corrige los errores',
        'errors' => $errores
    ]);
    exit;
}

// Configurar email
$para = 'naydelinvr6@gmail.com';
$asunto_email = 'Nuevo mensaje del portafolio: ' . $asunto;

// Crear el cuerpo del mensaje
$cuerpo = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00d9ff 0%, #00ffa3 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #e0e0e0; }
        .field { margin-bottom: 20px; }
        .field-label { font-weight: bold; color: #00d9ff; margin-bottom: 5px; }
        .field-value { background: white; padding: 10px; border-left: 3px solid #00d9ff; }
        .footer { background: #0a0e14; color: #8892b0; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📧 Nuevo Mensaje del Portafolio</h2>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='field-label'>👤 Nombre:</div>
                <div class='field-value'>" . htmlspecialchars($nombre) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>📧 Email:</div>
                <div class='field-value'>" . htmlspecialchars($email) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>📌 Asunto:</div>
                <div class='field-value'>" . htmlspecialchars($asunto) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>💬 Mensaje:</div>
                <div class='field-value'>" . nl2br(htmlspecialchars($mensaje)) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>🕐 Fecha:</div>
                <div class='field-value'>" . date('d/m/Y H:i:s') . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>Este mensaje fue enviado desde tu portafolio web</p>
            <p>naydelinvr.github.io</p>
        </div>
    </div>
</body>
</html>
";

// Cabeceras del email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Portafolio Web <noreply@naydelinvenegas.com>" . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Intentar enviar el email
$enviado = mail($para, $asunto_email, $cuerpo, $headers);

if ($enviado) {
    // Guardar en archivo de log (opcional)
    $log_entry = date('Y-m-d H:i:s') . " - Mensaje de: $nombre ($email) - Asunto: $asunto\n";
    file_put_contents('contact_log.txt', $log_entry, FILE_APPEND);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => '¡Mensaje enviado con éxito! Te contactaré pronto.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar el mensaje. Por favor intenta de nuevo o contáctame directamente por email.'
    ]);
}
?>
