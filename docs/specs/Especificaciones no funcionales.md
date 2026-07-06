# Especificaciones no funcionales

## Acerca de los elementos del sistema

No es necesario que los usuarios del sistema se autentiquen (aunque en
algún caso só podría ser necesario que se identifiquen).

La carga de productos será realizada por un sistema externo que
interactuará directamente con el origen de datos.

La carga de stock de almacén será realizada por un sistema externo que
interactuará directamente con el origen de datos.

La carga de ofertas será realizada por un sistema externo que
interactuará directamente con el origen de datos.

Las cajeras deberán contar con una interfaz web mediante la cual realizar el
cobro de los productos

El cierre de caja debe ser accesible mediante el navegador.

Los repositores deben poder revisar el listado de productos y stock y registrar
las reposiciones desde la terminal.

Todos los datos cargados por los usuarios deben poder almacenarse de manera persistente (incluidas las alertas de stock).

## Acerca de las tecnologías

El sistema debe estar desarrollado, principalmente, en PHP 8.0 y
nginx como webserver.

El código debe respetar el estilo especificado por el [PSR-1](https://www.php-fig.org/psr/psr-1/) y [PSR-12](https://www.php-fig.org/psr/psr-12/).

El sistema debe utilizar composer como gestor de dependencias y sistema de
autoloading.

Se sugiere ademas respetar el [PSR-4](https://www.php-fig.org/psr/psr-4/) para el autoloading asistido por composer.

Dependiendo de como decidan diagramar su aplicación podrían ser relevantes los
PSR: 3, 7, 11, 14, 15 y 17. Tengan en cuenta que el uso de los mismos solo se
aconseja si les facilita el trabajo, si respetar estos PSRs les agrega
complejidad a fines del ejercicio es preferible ignorarlos.

Se prohíbe el uso de frameworks, ORMs, ODMs y bibliotecas para generar y usar mocks, stubs o test doubles.  
El uso de cualquier otro tipo de biblioteca de terceros esta permitido.

Todo el código PHP de la capa de dominio debe contar con tests unitarios.

El uso de otros lenguajes de programación (como Javascript) y cualquier
biblioteca/framework para las partes del sistema con las que los usuarios
interactuarán esta permitido (pero no es donde se debe poner el foco del).

El sistema debe utilizar archivos de texto plano (por ej: JSON) almacenados en disco como
origen de datos.

Usen git!!!

## Acerca de la arquitectura

Debe seguirse la arquitectura propuesta por Domain Driven Design.

### Conceptos y componentes notables de DDD

- Lenguaje ubicuo
- Capas (Dominio, Infraestructura, Aplicación y Presentación)
- Indirection
- Abstracciones (interfaces) e implementaciones (clases que las implementan)
- Invariants
- Integrity (valid state) and consistency (valid state between components)
- Transactions
- Aggregates
- Entities
- Value objects
- Events

## Acerca de las metodologías de trabajo

No es obligatorio pero podría ser una buena oportunidad para
probar Test Driven Development.

## Acerca de la nomenclatura

Los nombres de todos los componentes que formen el sistema (clases, interfaces,
métodos, propiedades, variables, etc) deben estar escritos en español.

Se aceptaran términos en ingles únicamente cuando sean completamente técnicos y carezcan de traducción al lenguaje ubicuo del dominio, por ejemplo:

- Id (Identifier)
- Interface
- Abstract
- Trait
- View

## Método de "evaluación"

La arquitectura de sistemas es algo muy subjetivo, en la mayoría de los casos
no se pueden tomar malas o buenas decisiones (aunque si hay decisiones
mejores que otras para lograr ciertos objetivos) por lo que en cada revisión
de avance lo que haremos es revisar y cuestionar cada una de las decisiones que
hayan tomado para que puedan justificarlas.

## Nota importante

Pueden utilizarse todas agregarse tantos elementos como se desee (aunque no
estén mencionados en la especificación funcional), de la misma manera es
completamente válido ignorar algunos de los conceptos mencionados en la
especificación. Lo único obligatorio es implementar los casos de uso que
allí se mencionan.
