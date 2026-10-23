<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Comparte el renderizado, los mensajes y la protección CSRF de los controladores.
 */
abstract class AbstractController
{
    /**
     * Renderiza una vista dentro del diseño común.
     *
     * @param string $vista Ruta de vista relativa a views.
     * @param array<string, mixed> $datos Datos disponibles para la vista.
     * @param string $mensaje Mensaje opcional para mostrar al usuario.
     * @param string $tipoMensaje Tipo visual del mensaje.
     */
    protected function renderizar(
        string $vista,
        array $datos = [],
        string $mensaje = '',
        string $tipoMensaje = 'exito'
    ): void {
        if ($mensaje === '' && isset($_SESSION['flash'])) {
            $mensaje = (string) ($_SESSION['flash']['mensaje'] ?? '');
            $tipoMensaje = (string) ($_SESSION['flash']['tipo'] ?? 'exito');
            unset($_SESSION['flash']);
        }

        $csrfToken = (string) ($_SESSION['csrf_token'] ?? '');
        $tituloPagina = 'La Esquina | Ventas e inventario';
        $e = static fn (mixed $valor): string => htmlspecialchars(
            (string) $valor,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
        extract($datos, EXTR_SKIP);

        ob_start();
        require dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $vista);
        $contenido = (string) ob_get_clean();

        require dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'layout.php';
    }

    /**
     * Verifica el token CSRF enviado por un formulario.
     */
    protected function csrfValido(): bool
    {
        $esperado = (string) ($_SESSION['csrf_token'] ?? '');
        $recibido = $_POST['csrf_token'] ?? null;

        return $esperado !== ''
            && is_string($recibido)
            && hash_equals($esperado, $recibido);
    }

    /**
     * Redirige mediante POST/Redirect/GET y conserva un mensaje temporal.
     */
    protected function redirigir(string $accion, string $mensaje, string $tipo = 'exito'): void
    {
        $_SESSION['flash'] = [
            'mensaje' => $mensaje,
            'tipo' => $tipo,
        ];
        header('Location: index.php?accion=' . rawurlencode($accion), true, 303);
        exit;
    }

    /**
     * Devuelve un campo POST solo cuando contiene texto escalar.
     */
    protected function campoPost(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }
}
