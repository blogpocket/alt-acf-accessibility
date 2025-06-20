=== Alt & Accesibilidad Automática ===
Contributors: Antonio
Tags: accessibility, images, ACF, alt, aria-label, forms, headings
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Añade alt a imágenes sin él o con alt vacío usando "Una imagen cuyo archivo se llama <nombre>", sustituye aria-hidden en enlaces que envuelven imágenes por aria-label, añade etiquetas accesibles a controles de formulario (incluyendo textarea) y corrige encabezados vacíos.

También agrega un arreglo del lado del cliente para textareas inyectados por JavaScript (e.g., reCAPTCHA).

== Installation ==
1. Copia la carpeta del plugin `alt-acf-accessibility` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú “Plugins” en el Escritorio de WordPress.
3. Verifica que las imágenes, formularios (incluyendo textareas dinámicos) y encabezados de tu sitio ahora cumplen con WCAG.

== Changelog ==
= 1.1 =
* Inyección de alt con formato descriptivo.
* Eliminación de aria-hidden y adición de aria-label en enlaces de imágenes.
* Etiquetas de formulario sin label: inputs, selects y server-side textareas reciben aria-label (fallback "Área de texto" para textareas sin name/id).
* Encabezados vacíos (<h1>–<h6>) ahora incluyen un span oculto con texto "Encabezado".
* Fix client-side para textareas inyectados por JS (e.g., reCAPTCHA).
