# ClanWar Plugin

**ClanWar** es un plugin avanzado para **PocketMine-MP** que permite a los jugadores unirse a clanes y participar en épicas guerras entre clanes. Ofrece un sistema de facciones integrado, celebraciones visuales tras la victoria y eliminación automática de clanes inactivos o vacíos.

## Descripción General

El plugin permite la creación de clanes basados en facciones y organiza guerras entre estos. Los jugadores pueden unirse a las guerras y competir por la victoria en equipo. Después de cada guerra, se realiza una celebración visual y sonora, seguida de la eliminación de los jugadores del clan y del propio clan después de 6 segundos.

### Características

- **Sistema de facciones**: Los jugadores pueden crear y unirse a clanes basados en facciones.
- **Guerras de clanes**: Compite en guerras organizadas entre clanes.
- **Eliminación automática**: Los clanes vacíos o sin miembros activos son eliminados.
- **Efectos visuales**: Partículas y sonidos épicos tras la victoria.
- **Sistema de sesiones**: Cada jugador tiene una sesión que gestiona su estado en la guerra.

## Requisitos del Servidor

- **PocketMine-MP** (Servidor para Minecraft Bedrock)
- **Apache** como servidor web (si se está utilizando para la gestión de logs o interfaz de administración del plugin)
- **Commando**: Librería de comandos para PocketMine (`CortexPE\Commando`)
- **Sistema de Facciones** (`rxduz\factions`)

### Instalación del Plugin

1. Descarga el archivo `.phar` del plugin o clona el repositorio.
2. Coloca el archivo `.phar` en la carpeta `plugins/` de tu servidor **PocketMine-MP**.
3. Reinicia el servidor o ejecuta `/reload` para cargar el plugin.

### Configuración de Apache

Si tu equipo está utilizando **Apache** para tareas de gestión (por ejemplo, logs o una interfaz administrativa):

- Asegúrate de que Apache esté configurado correctamente en el servidor.
- Verifica que la carpeta de instalación del plugin tenga los permisos necesarios para lectura/escritura si Apache necesita acceder a archivos generados por el plugin.
- Si es necesario, configura un **VirtualHost** en Apache para administrar las interfaces del plugin.
