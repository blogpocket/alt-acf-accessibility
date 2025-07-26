<?php
/**
 * Plugin Name:     Alt & Accesibilidad Automática
 * Description:     Versión 1.1.1 – Inyección de alt descriptivo en imágenes, aria-label en enlaces de imágenes, aria-label en formularios y corrección de encabezados vacíos.
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
    // 1) Imágenes sin alt o con alt vacío
    $html = preg_replace_callback(
        '/<img\s+[^>]*>/i',
        function( $m ) {
            $img = $m[0];
            if ( preg_match('/\balt\s*=\s*(["\'])(.*?)\1/i', $img, $a) && strlen(trim($a[2]))>0 ) {
                return $img;
            }
            if ( preg_match('/\bsrc=(["\'])(.*?)\1/i', $img, $s) ) {
                $file = basename(parse_url($s[2], PHP_URL_PATH));
                $alt  = 'Una imagen cuyo archivo se llama ' . $file;
                $attr = esc_attr($alt);
                if ( preg_match('/\balt\s*=/i', $img) ) {
                    $img = preg_replace(
                        '/\balt\s*=\s*(["\'])(.*?)\1/i',
                        'alt="' . $attr . '"',
                        $img
                    );
                } else {
                    $img = preg_replace(
                        '/<img\s+/i',
                        '<img alt="' . $attr . '" ',
                        $img
                    );
                }
            }
            return $img;
        },
        $html
    );

    // 2) Enlaces que envuelven imágenes
    $html = preg_replace_callback(
        '/<a\b[^>]*>.*?<img\s+[^>]*>.*?<\/a>/is',
        function( $m ) {
            $a = $m[0];
            $a = preg_replace('/\saria-hidden=(["\'])(.*?)\1/i','',$a);
            if ( preg_match('/<img[^>]*\balt=(["\'])(.*?)\1/i',$a,$i) ) {
                $label = esc_attr($i[2]);
                if ( ! preg_match('/\baria-label=/i',$a) ) {
                    $a = preg_replace(
                        '/<a\b/i',
                        '<a aria-label="' . $label . '"',
                        $a,
                        1
                    );
                }
            }
            return $a;
        },
        $html
    );

    // 3) Formularios (input, textarea, select)
    $html = preg_replace_callback(
        '/<(input|textarea|select)\b[^>]*>/i',
        function( $m ) {
            $tag = $m[0];
            $el  = strtolower($m[1]);
            if ( $el==='input' && preg_match('/\btype=(["\']?)(hidden|submit|reset|button|image)\1/i',$tag) ) {
                return $tag;
            }
            if ( preg_match('/\b(aria-label|aria-labelledby|title)=/i',$tag) ) {
                return $tag;
            }
            if ( preg_match('/\bplaceholder=(["\'])(.*?)\1/i',$tag,$p) ) {
                $label = $p[2];
            } elseif ( preg_match('/\bname=(["\'])(.*?)\1/i',$tag,$n) ) {
                $label = $n[2];
            } elseif ( preg_match('/\bid=(["\'])(.*?)\1/i',$tag,$i) ) {
                $label = $i[2];
            } elseif ( $el==='textarea' ) {
                $label = 'Área de texto';
            } else {
                return $tag;
            }
            $aria = 'aria-label="' . esc_attr($label) . '"';
            return preg_replace('/<' + el + '\b/i', '<' + el + ' ' + $aria, $tag, 1);
        },
        $html
    );

    // 4) Encabezados h2-h6 vacíos
    $html = preg_replace_callback(
        '/<(h[2-6])\b([^>]*)>(.*?)<\/\1>/is',
        function( $m ) {
            $tag   = $m[1];
            $attr  = $m[2];
            $inner = $m[3];
            $text  = trim(strip_tags(preg_replace('/<br\s*\/?/i','',$inner)));
            if ( $text === '' ) {
                return "<{$tag}{$attr}><span class=\"screen-reader-text\">Encabezado</span></{$tag}>";
            }
            return $m[0];
        },
        $html
    );

    return $html;
}
// 5) Fallback JS para <img> dinámicas sin alt
add_action( 'wp_footer', function(){
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('img:not([alt])').forEach(function(img){
            var src = img.getAttribute('src') || '';
            var filename = src.split('/').pop() || 'imagen';
            img.setAttribute('alt', 'Una imagen cuyo archivo se llama ' + filename);
        });
    });
    </script>
    <?php
}, 100 );
