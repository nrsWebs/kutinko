Este módulo se encarga de modificar el comportamiento del incremental id de los pedidos, facturas, envíos y creditmemo.
1 - Magento 2.3.5-p1 asocia a cada store_view un prefijo al incremental id con el identificador de la tienda, se ha sustituido por NULL.
Afectando a la tabla de la base de datos: sales_sequence_profile.
2 - Magento 2.3.5-p1 asocia a cada tienda una serie de tablas donde se indica el incremental id utilizados, de esta manera se puede saber el siguiente, estas tablas son:
    - sequence_order_<store_id>
    - sequence_invoice_<store_id>
    - sequence_creditmemo_<store_id>
    - sequence_shipment_<store_id>

Para que todas las store view sigan la misma secuencia asignamos a store_id el identificador 0 (Admin).

Mejoras:
    - No fijar valores, sino hacerlo por store group, esto conlleva que pedidos/modificaciones por panel de administración seguirán su secuencia.