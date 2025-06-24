<?php
/**
 * Plugin Name:     Alt & Accesibilidad Automática
 * Description:     Añade alt a imágenes sin él o con alt vacío usando "Una imagen cuyo archivo se llama <nombre>", elimina aria-hidden y añade aria-label en enlaces que envuelven imágenes, añade aria-label a controles de formulario y corrige encabezados vacíos.
 * Version:         1.1.1
 * Author:          Antonio
 */

add_action( 'template_redirect', 'aaac_start_buffer' );
function aaac_start_buffer() {
    if ( ! is_admin() ) {
        ob_start( 'aaac_filter_output' );
    }
}

function aaac_filter_output( $html ) {
    // 1) Imágenes: inyectar alt descriptivo si falta o está vacío
    $html = preg_replace_callback(
        '/<img\s+[^>]*>/i',
        function( $matches ) {
            $img = $matches[0];
            // Conservar alt si ya existe y no está vacío
            if ( preg_match( '/\balt\s*=\s*("|\')(.*?)\1/i', $img, $am ) && strlen( trim( $am[2] ) ) > 0 ) {
                return $img;
            }
            // Obtener src y nombre de fichero
            if ( preg_match( '/\bsrc=("|\')(.*?)\1/i', $img, $m ) ) {
                $filename = basename( parse_url( $m[2], PHP_URL_PATH ) );
                $alt_text = 'Una imagen cuyo archivo se llama ' . $filename;
                $alt_attr = esc_attr( $alt_text );
                // Reemplazar o insertar alt
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

    // 2) Enlaces <a> que envuelven imágenes: eliminar aria-hidden y añadir aria-label
    $html = preg_replace_callback(
        '/<a\b[^>]*>\s*<img[^>]*>\s*<\/a>/i',
        function( $matches ) {
            $link = $matches[0];
            if ( preg_match( '/<img\b[^>]*\balt="([^"]+)"/i', $link, $am ) ) {
                $alt_text = $am[1];
                // Eliminar aria-hidden
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

    // 3) Controles de formulario: inputs, textarea, select reciben aria-label
    $html = preg_replace_callback(
        '/<(input|textarea|select)\b[^>]*>/i',
        function( $matches ) {
            $tag     = $matches[0];
            $element = strtolower( $matches[1] );
            $type    = '';
            // Detectar tipo input
            if ( $element === 'input' && preg_match( '/\btype=("|\')(\w+)\1/i', $tag, $t ) ) {
                $type = strtolower( $t[2] );
            }
            // Ocultar recaptcha textarea de Google
            if ( $element === 'textarea' && preg_match( '/\bg-recaptcha-response\b/i', $tag ) ) {
                // Añadir aria-hidden para omitir del tree de accesibilidad
                if ( ! preg_match( '/\baria-hidden=/i', $tag ) ) {
                    $tag = preg_replace( '/<textarea\b/i', '<textarea aria-hidden="true"', $tag, 1 );
                }
                return $tag;
            }
            // Omitir inputs que no requieren label
            if ( $element === 'input' && in_array( $type, [ 'hidden', 'submit', 'reset', 'button', 'image' ], true ) ) {
                return $tag;
            }
            // Omitir si ya tiene aria-label, aria-labelledby o title
            if ( preg_match( '/\b(aria-label|aria-labelledby|title)=/i', $tag ) ) {
                return $tag;
            }
            // Determinar texto para aria-label
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
            // Añadir aria-label
            return preg_replace(
                '/<' . $element . '\b/i',
                '<' . $element . ' aria-label="' . esc_attr( $label ) . '"',
                $tag,
                1
            );
        },
        $html
    );

    // 4) Encabezados vacíos <h2>-<h6> con contenido anidado vacío: span oculto
    $html = preg_replace_callback(
        '/<(h[2-6])\b([^>]*)>(.*?)<\/\1>/is',
        function( $matches ) {
            $tag   = $matches[1];
            $attrs = $matches[2];
            $inner = $matches[3];
            $text  = trim( strip_tags( preg_replace( '/<br\s*\/?>(?i)/', '', $inner ) ) );
            if ( $text === '' ) {
                return "<{$tag}{$attrs}><span class=\"screen-reader-text\">Encabezado</span></{$tag}>";
            }
            return $matches[0];
        },
        $html
    );

    return $html;
}
