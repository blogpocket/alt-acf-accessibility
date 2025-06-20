<?php
/**
 * Plugin Name:     Alt & Accesibilidad Automática
 * Description:     Añade alt a imágenes sin él o con alt vacío usando "Una imagen cuyo archivo se llama <nombre>", sustituye aria-hidden en enlaces que envuelven imágenes por aria-label, añade etiquetas accesibles a controles de formulario (excepto campos ocultos como recaptcha) y corrige encabezados vacíos.
 * Version:         1.1
 * Author:          Antonio
 */

add_action( 'template_redirect', 'aaac_start_buffer' );
function aaac_start_buffer() {
    if ( ! is_admin() ) {
        ob_start( 'aaac_filter_output' );
    }
}

function aaac_filter_output( $html ) {
    // 1) Fix imágenes sin alt o con alt vacío
    $html = preg_replace_callback(
        '/<img\s+[^>]*>/i',
        function( $matches ) {
            $img = $matches[0];

            // Eliminar aria-hidden si existe en la imagen
            $img = preg_replace( '/\saria-hidden=("|\')(.*?)\1/i', '', $img );

            // Si tiene alt con contenido, lo dejamos
            if ( preg_match( '/\balt\s*=\s*("|\')(.*?)\1/i', $img, $am ) && strlen( trim( $am[2] ) ) > 0 ) {
                return $img;
            }

            // Extraer src para obtener nombre de fichero
            if ( preg_match( '/\bsrc=("|\')(.*?)\1/i', $img, $m ) ) {
                $src      = $m[2];
                $filename = basename( parse_url( $src, PHP_URL_PATH ) );

                // Construir el alt
                $alt_text = 'Una imagen cuyo archivo se llama ' . $filename;
                $alt_attr = esc_attr( $alt_text );

                // Sustituir alt existente o insertarlo
                if ( preg_match( '/\balt\s*=/i', $img ) ) {
                    $img = preg_replace(
                        '/\balt\s*=\s*("|\')(.*?)\1/i',
                        'alt="' . $alt_attr . '"',
                        $img
                    );
                } else {
                    $img = preg_replace(
                        '/<img\s+/i',
                        '<img alt="' . $alt_attr . '" ',
                        $img
                    );
                }
            }

            return $img;
        },
        $html
    );

    // 2) Fix enlaces <a> que envuelven imágenes: sustituir aria-hidden por aria-label con valor del alt de la imagen
    $html = preg_replace_callback(
        '/<a\b[^>]*>\s*<img[^>]*>\s*<\/a>/i',
        function( $matches ) {
            $link = $matches[0];

            // Extraer alt de la imagen interna
            if ( preg_match( '/<img\b[^>]*\balt="([^"]+)"[^>]*>/i', $link, $am ) ) {
                $alt_text = $am[1];

                // Eliminar aria-hidden del <a>
                $link = preg_replace( '/\saria-hidden=("|\')(.*?)\1/i', '', $link );

                // Añadir aria-label si falta
                if ( ! preg_match( '/\baria-label=/i', $link ) ) {
                    $link = preg_replace(
                        '/<a\b/i',
                        '<a aria-label="' . esc_attr( $alt_text ) . '"',
                        $link,
                        1
                    );
                }
            }

            return $link;
        },
        $html
    );

    // 3) Fix controles de formulario sin label accesible (inputs, selects y textareas visibles)
    $html = preg_replace_callback(
        '/<(input|textarea|select)\b[^>]*>/i',
        function( $matches ) {
            $tag = $matches[0];
            $element = strtolower($matches[1]);

            // Omitir inputs ocultos y recaptcha (estilos display:none o class g-recaptcha-response)
            if ( $element === 'input' && preg_match('/\btype=("|\')(hidden|submit|reset|button|image)\1/i', $tag) ) {
                return $tag;
            }
            if ( $element === 'textarea' && preg_match('/\b(display\s*:\s*none)|(g-recaptcha-response)/i', $tag) ) {
                return $tag;
            }

            // Si ya tiene aria-label, aria-labelledby o title, no tocar
            if ( preg_match( '/\b(aria-label|aria-labelledby|title)=/i', $tag ) ) {
                return $tag;
            }

            // Determinar texto para aria-label: placeholder > name > id > fallback textarea
            $label = '';
            if ( preg_match( '/\bplaceholder=("|\')(.*?)\1/i', $tag, $p ) ) {
                $label = $p[2];
            } elseif ( preg_match( '/\bname=("|\')(.*?)\1/i', $tag, $n ) ) {
                $label = $n[2];
            } elseif ( preg_match( '/\bid=("|\')(.*?)\1/i', $tag, $i ) ) {
                $label = $i[2];
            } elseif ( $element === 'textarea' ) {
                $label = 'Área de texto';
            } else {
                return $tag;
            }

            return preg_replace(
                '/<' . $element . '\b/i',
                '<' . $element . ' aria-label="' . esc_attr( $label ) . '"',
                $tag,
                1
            );
        },
        $html
    );

    // 4) Fix encabezados vacíos <h1>-<h6>: añadir span oculta
    $html = preg_replace_callback(
        '/<(h[1-6])\b([^>]*)>(.*?)<\/\1>/is',
        function( $matches ) {
            $tag   = $matches[1];
            $attrs = $matches[2];
            $inner = $matches[3];
            $text  = trim( strip_tags( preg_replace('/<br\s*\/?>(?i)', '', $inner ) ) );
            if ( $text === '' ) {
                return "<{$tag}{$attrs}><span class=\"screen-reader-text\">Encabezado</span></{$tag}>";
            }
            return $matches[0];
        },
        $html
    );

    return $html;
}

// 5) Client-side fix para textareas inyectados por JavaScript (e.g., reCAPTCHA)
add_action( 'wp_enqueue_scripts', 'aaac_enqueue_form_label_fix' );
function aaac_enqueue_form_label_fix() {
    wp_register_script( 'aaac-form-label-fix', '', [], false, true );
    wp_add_inline_script( 'aaac-form-label-fix', 
        "document.addEventListener('DOMContentLoaded', function(){\n" .
        "  document.querySelectorAll('textarea:not([aria-label]):not([aria-labelledby]):not([title])').forEach(function(el){\n" .
        "    // omitir textareas ocultos (display:none)\n" .
        "    if(window.getComputedStyle(el).display==='none') return;\n" .
        "    var label = el.getAttribute('placeholder') || el.getAttribute('name') || el.getAttribute('id') || 'Área de texto';\n" .
        "    el.setAttribute('aria-label', label);\n" .
        "  });\n" .
        "});"
    );
    wp_enqueue_script( 'aaac-form-label-fix' );
}
