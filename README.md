## Requisitos
 
- PHP 8.3
- Composer
- MySQL o MariaDB

## Instalación
 
```bash
git clone https://github.com/alonsouribe/prosesamed
cd prosesamed
composer install
cp .env.example .env
```

Configurar en `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prosesamed
DB_USERNAME=***  root  ***
DB_PASSWORD=***  root  ***


QUEUE_CONNECTION=database
```

Crear la base de datos y correr migraciones:
```sql
CREATE DATABASE prosesamed;
```
```bash
php artisan migrate
```

## Cargar datos de prueba
 
Genera 500,000 ventas simuladas:
```bash
php artisan db:seed
```

## Levantar el proyecto
 
Se necesitan dos terminales abiertas al mismo tiempo:
 
```bash
# Terminal 1, servidor
php artisan serve
 
# Terminal 2, colas (necesario para el exportación)
php artisan queue:work
```

## Endpoints
 
| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/ventas?sucursal=5&status=1&por_pagina=10` | Listado paginado (keyset) |
| GET | `/api/ventas/offset?sucursal=5&status=1&pagina=1` | Listado paginado (OFFSET, solo era comparar) |
| GET | `/api/ventas/excel?sucursal=5&status=1` | Genera el reporte CSV (asíncrono). Responde con `reporte_id` |
| GET | `/api/reportes/{id}/descargar` | Descarga el reporte cuando su estado es `listo` |
 
## Correr pruebas
 
```bash
php artisan test
```

Pruebas unitarias del cálculo de saldo/deuda en `tests/Unit/CalculadoraDeSaldoTest.php`.

## Estructura (capas)
 
```
app/
-> Http/Controllers/ -> recibe el request y delega
-> Http/Requests/ -> validación de entrada
-> Domain/Venta/ -> reglas de negocio (CalculadoraDeSaldo, interfaces)
-> Application/Venta/ -> orquestación (Services)
-> Infrastructure/Persistence/ -> acceso a datos (Eloquent)
-> Jobs/ -> exportación asíncrono
-> Models/ -> modelos Eloquent
```
## Evidencia
 
Archivos de respaldo`:
 
- `evidencia_explain_antes.txt` resultado de `EXPLAIN` antes de corregir.
- `evidencia_explain_despues.txt` resultado de `EXPLAIN` después de la corrección.
- `evidencia_paginacion_offset.txt` / `evidencia_paginacion_keyset.txt` solo eran comparar rendimiento.

Los reportes se guardan en `storage/app/reportes/`.
