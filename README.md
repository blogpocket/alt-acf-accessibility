# Alt & Accesibilidad Automática

**Version:** 1.1.1  
**Author:** Antonio Cambronero (Blogpocket.com) 

## Description

Este plugin mejora la accesibilidad de tu sitio WordPress de forma automática:

1. **Imágenes** sin atributo `alt` o con `alt=""` reciben:
   ```html
   alt="Una imagen cuyo archivo se llama <nombre-del-archivo>"
   ```
2. **Enlaces** (`<a>`) que envuelven imágenes:
   - Se elimina cualquier `aria-hidden`
   - Se añade `aria-label="<texto del alt>"`
3. **Controles de formulario** (`<input>`, `<textarea>`, `<select>`):
   - Se omiten los tipos ocultos (`hidden`, `submit`, `reset`, `button`, `image`)
   - Se añade `aria-label` basado en `placeholder`, `name`, `id` o, en `<textarea>`, texto `"Área de texto"`
4. **Encabezados** (`<h2>`–`<h6>`) vacíos se corrigen añadiendo:
   ```html
   <span class="screen-reader-text">Encabezado</span>
   ```
5. **Fallback JS** para `<img>` dinámicas sin `alt` (incluye imágenes insertadas tras el filtrado PHP).

## Installation

1. Sube la carpeta `alt-acf-accessibility` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú **Plugins** en el Escritorio de WordPress.

## Usage

Al activarlo, el plugin filtrará automáticamente el HTML en el frontend y aplicará las mejoras de accesibilidad mencionadas.

## Changelog

### 1.1.1
- Versión inicial con inyección de atributos `alt` y `aria-label`.
- Corrección de encabezados vacíos.
- Añadido fallback JS para imágenes dinámicas sin `alt`.

## License

Este plugin está licenciado bajo GPLv2 o superior.
