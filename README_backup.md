# Estrategia de Backup — TalentHub

## Tipo: en caliente/lógico (mysqldump), no físico
Justificación: TalentHub se usa en horario laboral con consultas
concurrentes (empleados, supervisores), por lo que no puede detenerse
para hacer backup → backup en CALIENTE (el servicio sigue operativo).
Se eligió lógico (mysqldump) sobre físico porque es más simple de
restaurar en cualquier entorno y no depende del mismo motor de
almacenamiento. La opción `--single-transaction` es la que garantiza
consistencia sin bloquear tablas InnoDB — es decir, sin pasar a frío.

## Regla 3-2-1
- 3 copias: la BD en producción + 2 backups (local y externo/nube)
- 2 soportes distintos: disco local + almacenamiento en la nube (o USB externo)
- 1 copia fuera del sitio: subir periódicamente la carpeta de backups a
  Drive/Dropbox o similar

## Frecuencia
- Diaria, vía cron (Linux) o Programador de tareas (Windows), fuera de
  horario laboral (ej. 03:00 AM)
- Cron: `0 3 * * * /ruta/a/backup.sh`

## Retención
- 30 días de backups diarios (los más viejos se borran automáticamente)

## Restauración