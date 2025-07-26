# Alt & Accesibilidad Automática

**Versión:** 1.1.4  
**Autor:** Antonio Cambronero (Blogpocket.com)  

## Descripción

Este plugin mejora la accesibilidad de tu sitio WordPress de forma automática:

1. **Imágenes** sin atributo `alt` o con `alt=""` reciben:
   ```html
   alt="Una imagen cuyo archivo se llama <nombre-del-archivo>"
   ```
2. **Enlaces** (`<a>`) que envuelven imágenes:
   - Se elimina cualquier texto de diagnóstico `Linked image missing alternative text`
   - Se elimina cualquier `aria-hidden`
   - Se añade `aria-label="<texto del alt>"`
3. **Controles de formulario** (`<input>`, `<textarea>`, `<select>`):
   - Se omiten los tipos ocultos (`hidden`, `submit`, `reset`, `button`, `image`)
   - Se añade `aria-label` basado en `placeholder`, `name`, `id` o, en `<textarea>`, texto `"Área de texto"`
4. **Encabezados** (`<h2>`–`<h6>`) vacíos se corrigen añadiendo:
   ```html
   <span class="screen-reader-text">Encabezado</span>
   ```
5. **Fallback JS**:
   - Para `<img>` dinámicas sin `alt`
   - Para enlaces con imágenes que contengan texto de diagnóstico: elimina texto, limpia nodos de texto y añade `aria-label`

## Instalación

1. Sube la carpeta `alt-acf-accessibility` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú **Plugins** en el Escritorio de WordPress.

## Uso

Al activarlo, el plugin filtrará automáticamente el HTML en el frontend y aplicará las mejoras de accesibilidad mencionadas.

## Pruebas

Antes de usar este plugin en una instalación real o de producción, pruébalo en un entorno de test. Por favor, si detectas algún fallo o error, comunícalo al autor del plugin.

## Registro de cambios

### 1.1.4
* Fallback JS para enlaces con imágenes: limpia texto de diagnóstico y añade `aria-label`.
* Mantiene inyección de atributos `alt`, `aria-label` en formularios, encabezados vacíos y fallback JS para imágenes dinámicas.

## Licencia

Este plugin está licenciado bajo GPLv2 o superior.
