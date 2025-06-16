<?php
/**
 * Plugin Name:     Alt & Accesibilidad Automática
 * Description:     Añade alt a imágenes sin él o con alt vacío usando "Una imagen cuyo archivo se llama <nombre>", sustituye aria-hidden en enlaces que envuelven imágenes por aria-label, y añade etiquetas accesibles a formularios.
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

            // Si tiene alt con contenido, lo dejamos
            if ( preg_match( '/\balt\s*=\s*("|\')(.*?)(\1)/i', $img, $am ) && strlen( trim( $am[2] ) ) > 0 ) {
                return $img;
            }

            // Extraer src para obtener nombre de fichero
            if ( preg_match( '/\bsrc=("|\')(.*?)\1/i', $img, $m ) ) {
                $src      = $m[2];
                $filename = basename( parse_url( $src, PHP_URL_PATH ) );

                // Construir el alt con la frase deseada
                $alt_text = 'Una imagen cuyo archivo se llama ' . $filename;
                $alt_attr = esc_attr( $alt_text );

                // Sustituir alt existente (incluso vacío) o insertar uno nuevo
                if ( preg_match( '/\balt\s*=/', $img ) ) {
                    $img = preg_replace(
                        '/\balt\s*=\s*("|\')(.*?)(\1)/i',
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
                $label    = esc_attr( $alt_text );

                // Eliminar aria-hidden del <a>
                $link = preg_replace( '/\saria-hidden=("|\')(.*?)\1/i', '', $link );

                // Añadir aria-label si no existe
                if ( ! preg_match( '/\baria-label=/i', $link ) ) {
                    $link = preg_replace(
                        '/<a\b/i',
                        '<a aria-label="' . $label . '"',
                        $link,
                        1
                    );
                }
            }

            return $link;
        },
        $html
    );

    // 3) Fix controles de formulario sin label accesible
    $html = preg_replace_callback(
        '/<(input|textarea|select)\b[^>]*>/i',
        function( $matches ) {
            $tag  = $matches[0];
            $type = '';

            if ( preg_match( '/\btype=("|\')(\w+)(\1)/i', $tag, $t ) ) {
                $type = strtolower( $t[2] );
            }

            // Omitir tipos que no requieren label
            if ( in_array( $type, [ 'hidden', 'submit', 'reset', 'button', 'image' ] ) ) {
                return $tag;
            }

            // Si ya tiene aria-label, aria-labelledby o title, no tocar
            if ( preg_match( '/\b(aria-label|aria-labelledby|title)=/i', $tag ) ) {
                return $tag;
            }

            // Determinar texto para aria-label: placeholder > name > id
            if ( preg_match( '/\bplaceholder=("|\')(.*?)\1/i', $tag, $p ) ) {
                $label = $p[2];
            } elseif ( preg_match( '/\bname=("|\')(.*?)\1/i', $tag, $n ) ) {
                $label = $n[2];
            } elseif ( preg_match( '/\bid=("|\')(.*?)\1/i', $tag, $i ) ) {
                $label = $i[2];
            } else {
                return $tag;
            }

            $label_esc = esc_attr( $label );
            return preg_replace(
                '/<(input|textarea|select)\b/i',
                '<$1 aria-label="' . $label_esc . '"',
                $tag
            );
        },
        $html
    );

    return $html;
}
