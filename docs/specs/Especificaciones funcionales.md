# Especificaciones funcionales

Se solicita el desarrollo de un sistema para gestión de stock de supermercados.

A continuación se sumarizan los distintos actores y elementos del sistema que
se desea.

## Establecimiento

Establecimiento que utiliza el sistema, no se prevé la utilización por parte
de supermercados que cuenten con múltiples establecimientos.

## Cliente

Los clientes del establecimiento intercambian bienes físicos por dinero.

Los clientes recorren el establecimiento tomando productos de las góndolas y
depositándolos en sus carros. Una vez que tienen todos los productos que quieren
se dirigen a una caja para que se les cobre por ellos.

Son identificados mediante su nombre y apellido.

## Caja

La caja es el espacio físico en el que las cajeras atienden a los clientes,
aquí ocurre el cobro de los productos que el paciente lleva en su carro.

## Cajera

Las cajeras utilizan el sistema para cobrar los productos que los clientes
llevan a la caja.

La cajera registra en el sistema los productos que un cliente quiere y el sistema
le indica el precio total, teniendo en cuenta las ofertas vigentes.
Luego la venta se registra en el sistema.

## Cierre de Caja

El cierre de caja es una tabla, por caja y día, en la que cada fila representa una
venta de productos y en cuyas columnas se especifica: el cliente, el monto y moneda
cobrados y la cajera.

## Góndola

Las góndolas son estanterías ubicadas dentro del establecimiento donde se
exponen los productos para que los clientes los tomen y lleven a la caja
para su cobro.

## Producto

Un producto puede ser cualquier bien material que el establecimiento ofrezca.

Tienen un nombre y un precio compuesto por un monto y una moneda.

## Oferta

Una oferta es un cierto porcentaje de descuento que se aplica al precio de
un producto.

Las ofertas solo son validas durante un periodo determinado de tiempo.

## Repositor

Los repositores son los encargados de reponer los productos de las góndolas con
el stock existente en el almacén.

Para esto los respositores revisan un listado del stock actual de los productos
en las góndolas y reponen manualmente aquellos que tienen menos de 30 artículos
(y completan el faltante para alcanzar un stock de 50).

Luego de reponer el stock manualmente registran esta acción en el sistema.

## Almacén

El almacen es un deposito donde se almacena stock de todos los productos
que el establecimiento ofrece para poder reponer stock en las góndolas a
medida que es necesario.

## Alerta de stock

Cuando un repositor encuentra se dispone a reponer un cierto producto y
encuentra que en el almacen el stock es menor a 150 debe emitir un alerta para
la gestión del establecimiento y luego continuar con su labor habitual de
reposición.

## Casos de uso a cubrir

Las únicas acciones que deben ser cubiertas de manera obligatoria mediante las
interfaces de usuario del sistema son las descritas anteriormente en Cajera,
Cierre de caja, Repositor y Alerta de stock; es decir:

- [ ] Calculo de precios, según las ofertas vigentes.
- [ ] Registro de las ventas realizadas.
- [ ] Obtención del cierre de caja.
- [ ] Obtención de listado de stock.
- [ ] Registro de reposición de stock.
- [ ] Registro de alerta de stock.
