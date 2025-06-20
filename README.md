=== Alt & Accesibilidad Automática ===
Contributors: Antonio
Tags: accessibility, images, ACF, alt, aria-label, forms, headings
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Añade alt a imágenes sin él o con alt vacío usando "Una imagen cuyo archivo se llama <nombre>", sustituye aria-hidden en enlaces que envuelven imágenes por aria-label, añade etiquetas accesibles a controles de formulario (incluyendo textarea) y corrige encabezados vacíos insertando texto para lectores de pantalla.

== Installation ==
1. Copia la carpeta del plugin `alt-acf-accessibility` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú “Plugins” en el Escritorio de WordPress.
3. Comprueba que las imágenes, formularios y encabezados de tu sitio ahora cumplen con WCAG.

== Changelog ==
= 1.1 =
* Inyección de alt con formato descriptivo.
* Eliminación de aria-hidden y adición de aria-label en enlaces de imágenes.
* Etiquetas de formulario sin label: inputs, selects y textareas reciben aria-label (fallback "Área de texto" para textareas sin name/id).
* Encabezados vacíos (<h1>–<h6>) ahora incluyen un span oculto con texto "Encabezado" para accesibilidad.
