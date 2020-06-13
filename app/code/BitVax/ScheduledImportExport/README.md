# Objetivo

Automatizar la importación de productos mediante el cron de magento

* Identificador del cron: _bitvax_scheduled_import_

## Soporte
Este módulo se ayuda del módulo gratuito _ethanyehuda/magento2-cronjobmanager_ el cual nos ayuda a la gestión de todos los cronjobs de Magento.
El cual nos permite modificar el período de ejecución del cronjob

## Configuración

Se ha habilitado en el panel de administración de Magento un apartado para la configuración básica de la importación, siguiendo la configuración básica e imprescindible de una importación de productos en Magento.
Para ello hemos de dirigirnos a:

* Stores --> Settings --> Configuration --> BitVax --> Scheduled Import/Export

## Log

Se ha creado un log especifico para mostrar el progreso de la importación: <Magento_ROOT>/var/log/scheduled_import.log

## Sistema de ficheros

El módulo lee todos los archivos con extensión csv bajo la ruta:

* <Magento_ROOT>/var/scheduled_import

Si un archivo se importa correctamente se elimina del sistema de ficheros.


