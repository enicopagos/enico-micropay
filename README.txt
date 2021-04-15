=== Plugin Name ===
Contributors: viktormorales
Donate link: https://enico.info
Tags: comments, spam
Requires at least: 4.7
Tested up to: 5.7
Requires PHP: 7.0
Stable tag: 1.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

==Versión 1.5.0 (GitHub)==

Aquellos usuario que quieran actualizar de la versión 1.1 o 1.2 se recomienda que descarguen e instalen esta versión.

==Énico, paywall para contenido único==

Énico es un sistema de micropagos fácil de usar que permite comprar y vender contenido en internet mediante transacciones únicas. El muro de pago bloquea artículos individuales, permitiendo su pago a través de MercadoPago. Funciona en todos los países en los que existe MercadoPago/MercadoLibre.

==Características:==
- Indica el porcentaje de texto que queda detrás del paywall
- Texto configurable en los settings
- Precio fijo global, precio por entrada y precio mínimo
- Limitación para copiar y pegar texto y para imprimir.
- URL única por email
- "Antesala" para que el usuario se guarde la URL única en favoritos
- Rediseño del front end

==Instalación:==
- Subir el plugin desde el zip o desde el repositorio
- Activarlo
- Ir a configuración del plugin
- Seleccionar el país, completar el token de mercadopago y los textos personalizables.
- Presionar el botón de migrar configuraciones
- Ir a "enlaces permanentes" y guardar cambios

==Configuración:==
En el post en el que se quiera agregar un paywall, se debe activar y completar el plugin en la caja de la derecha y, dentro del post ingresar el tag  <!--more--> en el lugar exacto en el que se desea que aparezca el muro de pago.

==Preguntas Frecuentes:==
- ¿Donde encuentro el token de MercadoPago?
- En este tutorial se explica como encontrarlo: https://www.mercadopago.com.ar/developers/es/guides/resources/faqs/credentials

==Limitaciones y problemas conocidos:==
- Se recomienda tener instalado algún plugin de envío por SMTP para tener garantizada la llegada de los emails de notificación (por ejemplo WP Mail SMTP)
- Puede presentar conflictos con plugins de cache, tipo WP Super Cache