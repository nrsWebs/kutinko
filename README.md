Inicialización proyecto:

1. Restaurar backup de la base de datos
2. Copiar el archivo env.php bajo la ruta app/etc. Sobre este archivo es posible que se hayan de realizar cambios según si se utiliza redis o no.

Deploy genérico para magento 2, proyecto kutinko:

1. Trabajaremos con la rama master, por lo tanto para bajar los últimos cambios haremos:
  * $ git pull origin master
2. Realizamos la instalación los módulos de terceros vía composer que no se hayan instalado:
  * $ composer install
3. (Opcional) Realizamos el comando de magento para actualizar la base de datos en caso de que algún módulo lo requiera:
  * bin/magento setup:upgrade
4. Realizamos un borrado de caché y del contenido estático generado:
  * bin/magento c: && bin/magento c:f 
  * rm -rf generated/* && rm -rf var/view_preprocessed/* && rm -rf pub/static/*
5. [Magento en modo PRODUCTION] Realizamos la generación del contenido estático
  * bin/magento setup:di:compilo
  * bin/magento setup:static-content:deploy en_US es_ES

