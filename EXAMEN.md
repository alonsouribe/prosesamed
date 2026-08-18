php -v
https://windows.php.net/download
crear carpeta en C:\php (v8.3)
crear una copia de php.ini-development a renombrar php.ini
configurar archivo php.ini
- extension=curl
- extension=fileinfo
- extension=mbstring
- extension=openssl
- extension=pdo_mysql
- extension=zip
agregar C:\php en variables de entorno en Path

NOTA: si se instala PHP 8.4 genera errores aunque se puede trabajar, lo ideal es que sea la versión 8.3

mysql --version
https://dev.mysql.com/downloads/installer

abrir cmd y conectar a mysql
mysql -u root -P 3307 -p

password root

crear base de datos
CREATE DATABASE prosesamed;

configurar base de datos en el archivo .env

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=prosesamed
DB_USERNAME=root
DB_PASSWORD=root

composer -v
https://getcomposer.org/download

crear proyecto con laravel v10
composer create-project laravel/laravel prosesamed "10.*"

NOTA: si se instala php v8.4 con laravel v10, marca error pero se puede avanzar ejecutando los siguientes comandos:

composer config --global audit.abandoned ignore
composer config --global policy.advisories.block false

para iniciar el server se ejecuta:
php artisan serve

crear tabla ventas
php artisan make:migration create_ventas_table

crear tabla pagos
php artisan make:migration create_pagos_table

crear tabla mensualidad
php artisan make:migration create_mensualidades_table

revisar en database/migrations si se crearon las tablas

crear tablas en la base de datos
php artisan migrate

revisar si se crearon las tablas
mysql -u root -P 3307 -p
USE prosesamed;
SHOW TABLES;

crear modelo de venta
php artisan make:model Venta

crear modelo de pago
php artisan make:model Pago

crear modelo de mensualidad
php artisan make:model Mensualidad

revisar en app/Models si se crearon los modelos

generar datos de prueba y por lote
php artisan make:factory VentaFactory --model=Venta

revisar en database/factories si se creo el factory

configurar seed para generar 500k registros en database/seeders/DatabaseSeeder.php

ejecutar el seeder
php artisan db:seed

NOTA: 
si sale este error: PHP Fatal error:  Allowed memory size of 134217728 bytes exhausted (tried to allocate 802816 bytes) in C:\prosesamed\vendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php on line 45

en el archivo php.init cambiar el parametro memory_limit de 128M a 512M y como trono, hay que limpiar la tabla de ventas

mysql -u root -P 3307 -p
USE prosesamed;
TRUNCATE TABLE ventas;

checar si se cargaron los datos
mysql -u root -P 3307 -p
USE prosesamed;
SELECT COUNT(*) FROM ventas;

mysql> SELECT COUNT(*) FROM ventas;
+----------+
| COUNT(*) |
+----------+
|   500000 |
+----------+
1 row in set (0.03 sec)

levantar para probar datos
php artisan serve

obtener evidencia explain
mysql -u root -P 3307 -p
USE prosesamed;
EXPLAIN SELECT * FROM ventas WHERE id_sucursal = '5' AND status = 1 ORDER BY fecha_venta;

al revisar que todos los registros, ya se aprecia los 500k registros

mysql> EXPLAIN SELECT * FROM ventas WHERE id_sucursal = '5' AND status = 1 ORDER BY fecha_venta;
+----+-------------+--------+------------+------+---------------+------+---------+------+--------+----------+-----------------------------+
| id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows   | filtered | Extra                       |
+----+-------------+--------+------------+------+---------------+------+---------+------+--------+----------+-----------------------------+
|  1 | SIMPLE      | ventas | NULL       | ALL  | NULL          | NULL | NULL    | NULL | 498846 |     1.00 | Using where; Using filesort |
+----+-------------+--------+------------+------+---------------+------+---------+------+--------+----------+-----------------------------+
1 row in set, 1 warning (0.00 sec)

creamos una migracion para corregir
php artisan make:migration fix_ventas_column_types

con la correcion de tipo de dato, revisamos de nuevo explain

mysql -u root -P 3307 -p
USE prosesamed;
EXPLAIN SELECT * FROM ventas WHERE id_sucursal = 5 AND status = 1 ORDER BY fecha_venta;


se puede apreciar que rows es menor a la consulta anterior

mysql> EXPLAIN SELECT * FROM ventas WHERE id_sucursal = 5 AND status = 1 ORDER BY fecha_venta;
+----+-------------+--------+------------+------+-------------------------+-------------------------+---------+-------------+------+----------+-------+
| id | select_type | table  | partitions | type | possible_keys           | key                     | key_len | ref         | rows | filtered | Extra |
+----+-------------+--------+------------+------+-------------------------+-------------------------+---------+-------------+------+----------+-------+
|  1 | SIMPLE      | ventas | NULL       | ref  | idx_ventas_filtro_orden | idx_ventas_filtro_orden | 5       | const,const | 4966 |   100.00 | NULL  |
+----+-------------+--------+------------+------+-------------------------+-------------------------+---------+-------------+------+----------+-------+
1 row in set, 1 warning (0.00 sec)

revisar en database/migrations si se creo el archivo para hacer las correciones

ejecutamos la migracion para modificar la tabla 
php artisan migrate

para refactorizar y aplicar la arquitectura limpia, se crean 3 carpetas: Domain, Applicatiom e Infrastructure

se crea un archivo repository para definir el contrato (interface):

crear manualmente app/Domain/Venta/VentaRepositoryInterface.php

en laravel v10 no es valido el comando
php artisan make:interface Domain/Venta/VentaRepositoryInterface
php artisan make:interface --help


luego creamos el repository con su implementacion:

crear manualmente app/Infrastructure/Persistence/EloquentVentaRepository.php

en laravel v10 no es valido el comando
php artisan make:class Infrastructure/Persistence/EloquentVentaRepository
php artisan make:class --help

creamos una fuente unica para calculcar el saldo
crear manualmente app/Domain/Venta/CalculadoraDeSaldo.php

creamos el servicio
crear manualmente app/Application/Venta/ListarVentasService.php

creamos el request 
php artisan make:request ListarVentasRequest

revisamos que se haya creado y para validar el request (sucursal y status)
app/Http/Requests/ListarVentasRequest.php


creamos el controller
php artisan make:controller VentasController

conectar repository con eloquent
en app/Providers/AppServiceProvider.php

si aparece este error:
Illuminate \ Database \ QueryException
PHP 8.3.33 10.50.3 SQLSTATE[42S02]: Base table or view not found: 1146 Table 'prosesamed.mensualidads' doesn't exist
select * from `mensualidads` where `id_cotizacion` in (1528, 2419, 179, 3208, 2031, 61, 3451, 4163, 3097, 1942)

se resuelve agregando esto al modelo:

protected $table = 'Mensualidades';

dar de alta la ruta de ventas
routes/api.php

primera prueba de data, por key/set
http://127.0.0.1:8000/api/ventas?sucursal=5&status=1&por_pagina=10
{"ventas":[{"id":421652,"id_sucursal":5,"status":1,"id_cotizacion":1528,"monto":"30389.86","fecha_venta":"2026-08-17","created_at":null,"updated_at":null,"deuda":30389.86},{"id":332635,"id_sucursal":5,"status":1,"id_cotizacion":2419,"monto":"26458.30","fecha_venta":"2026-08-17","created_at":null,"updated_at":null,"deuda":26458.3},{"id":209199,"id_sucursal":5,"status":1,"id_cotizacion":179,"monto":"39763.37","fecha_venta":"2026-08-17","created_at":null,"updated_at":null,"deuda":39763.37},{"id":196824,"id_sucursal":5,"status":1,"id_cotizacion":3208,"monto":"33068.42","fecha_venta":"2026-08-17","created_at":null,"updated_at":null,"deuda":33068.42},{"id":193298,"id_sucursal":5,"status":1,"id_cotizacion":2031,"monto":"6896.04","fecha_venta":"2026-08-17","created_at":null,"updated_at":null,"deuda":6896.04},{"id":466884,"id_sucursal":5,"status":1,"id_cotizacion":61,"monto":"40597.75","fecha_venta":"2026-08-16","created_at":null,"updated_at":null,"deuda":40597.75},{"id":365160,"id_sucursal":5,"status":1,"id_cotizacion":3451,"monto":"32647.36","fecha_venta":"2026-08-16","created_at":null,"updated_at":null,"deuda":32647.36},{"id":236788,"id_sucursal":5,"status":1,"id_cotizacion":4163,"monto":"34645.30","fecha_venta":"2026-08-16","created_at":null,"updated_at":null,"deuda":34645.3},{"id":134430,"id_sucursal":5,"status":1,"id_cotizacion":3097,"monto":"32345.77","fecha_venta":"2026-08-16","created_at":null,"updated_at":null,"deuda":32345.77},{"id":12418,"id_sucursal":5,"status":1,"id_cotizacion":1942,"monto":"17528.81","fecha_venta":"2026-08-16","created_at":null,"updated_at":null,"deuda":17528.81}],"siguiente_cursor":"2026-08-16"}

http://127.0.0.1:8000/api/ventas/offset?sucursal=5&status=1&pagina=1&por_pagina=10
{"ventas":[{"id":199954,"id_sucursal":5,"status":1,"id_cotizacion":4733,"monto":"26562.39","fecha_venta":"2024-08-18","created_at":null,"updated_at":null,"deuda":26562.39},{"id":232540,"id_sucursal":5,"status":1,"id_cotizacion":3289,"monto":"36108.92","fecha_venta":"2024-08-18","created_at":null,"updated_at":null,"deuda":36108.92},{"id":282108,"id_sucursal":5,"status":1,"id_cotizacion":3084,"monto":"32760.18","fecha_venta":"2024-08-18","created_at":null,"updated_at":null,"deuda":32760.18},{"id":327345,"id_sucursal":5,"status":1,"id_cotizacion":2483,"monto":"22484.33","fecha_venta":"2024-08-18","created_at":null,"updated_at":null,"deuda":22484.33},{"id":380589,"id_sucursal":5,"status":1,"id_cotizacion":3480,"monto":"17604.72","fecha_venta":"2024-08-18","created_at":null,"updated_at":null,"deuda":17604.72},{"id":404300,"id_sucursal":5,"status":1,"id_cotizacion":4075,"monto":"2522.36","fecha_venta":"2024-08-18","created_at":null,"updated_at":null,"deuda":2522.36},{"id":447041,"id_sucursal":5,"status":1,"id_cotizacion":914,"monto":"25823.62","fecha_venta":"2024-08-18","created_at":null,"updated_at":null,"deuda":25823.62},{"id":42078,"id_sucursal":5,"status":1,"id_cotizacion":3511,"monto":"38890.01","fecha_venta":"2024-08-19","created_at":null,"updated_at":null,"deuda":38890.01},{"id":111219,"id_sucursal":5,"status":1,"id_cotizacion":1875,"monto":"3317.92","fecha_venta":"2024-08-19","created_at":null,"updated_at":null,"deuda":3317.92},{"id":209181,"id_sucursal":5,"status":1,"id_cotizacion":952,"monto":"26623.21","fecha_venta":"2024-08-19","created_at":null,"updated_at":null,"deuda":26623.21}]}

para crear las test del saldo, ejecutamos los comandos:
php artisan make:test CalculadoraDeSaldoTest --unit

verificamos que el archivo se encuentre en tests/Unit/CalculadoraDeSaldoTest.php

para revisar si la test funciona, ejecutar
php artisan test


para crear la tabla de reportes, se ejecuta el comando
php artisan make:migration create_reportes_table

revisar si se creo el archivo en database/migrations

creamos el modelo de reporte
revisamos app/Models

y ejecutamos para crear la tabla
php artisan migrate

generamos un job para poner la ejecucion en segundo plano
php artisan make:job GenerarReporteVentasJob

revisamos la creacion en app/Jobs/GenerarReporteVentasJob.php

agregamos las rutas para probar
Route::get('/ventas/excel', [VentasController::class, 'excel']);
Route::get('/reportes/{id}/descargar', [VentasController::class, 'descargarReporte']);


antes que todo ejecutar esto, para crear las tablas de jobs
php artisan queue:table


y modificar .env
QUEUE_CONNECTION=database
para que se guarde lios jobs en la tabla


para probar el job al generar un reporte, levantamos el server
php artisan serve

y en otra terminal
php artisan queue:work

para probar el servicio accedemos a:
http://127.0.0.1:8000/api/ventas/excel?sucursal=5&status=1

y debe crearte el reporte en storage/app/reportes/reporte_1.csv

e igual se puede revisar desde mysql

mysql -u root -P 3307 -p
USE prosesamed;
SELECT id, status, path FROM reportes ORDER BY id DESC LIMIT 1;

para descargar el reporte, accedemos a
http://127.0.0.1:8000/api/reportes/1/descargar

en donde 1, es el id del reporte
