📦 Resumen del Proyecto: Clickmarket
Clickmarket es una aplicación web de gestión de inventario diseñada para pequeñas y medianas empresas. Permite administrar productos, controlar niveles de stock, gestionar categorías y usuarios, y generar reportes, todo a través de una interfaz moderna y amigable.

🛠️ Tecnologías Utilizadas (Stack Tecnológico)
El proyecto está construido utilizando tecnologías web nativas, sin depender de frameworks pesados, lo que garantiza un rendimiento rápido y facilidad de despliegue.

Lenguaje Backend: PHP (Nativo).

Manejo de sesiones para autenticación.

Programación estructurada y modular (config.php, procesar.php).

Base de Datos: MySQL.

Conexión segura mediante PDO (PHP Data Objects) para prevenir inyecciones SQL.

Frontend (Interfaz):

HTML5 semántico.

CSS3 personalizado. Sin frameworks externos como Bootstrap (todo el estilo es propio en styles.css).

Interactividad: JavaScript.

Manipulación del DOM para modales y notificaciones.

✨ Funcionalidades Clave
1. Gestión de Productos (CRUD Completo)
Crear: Alta de nuevos productos con nombre, precio, stock y categoría.

Leer: Listado paginado de productos (10 por página) para manejar grandes volúmenes de datos.

Actualizar: Edición rápida mediante modales pre-llenados con AJAX.

Eliminar: Borrado de productos con confirmación de seguridad.

2. Control de Stock Inteligente
Semáforo de Estado: Visualización gráfica del estado del stock:

🟢 En Stock: Verde.

🟡 Stock Bajo: Amarillo (< 5 unidades).

🔴 Sin Stock: Rojo (0 unidades).

Dashboard: Tarjetas estadísticas en tiempo real (Total de productos, alertas de stock).

3. Búsqueda y Filtros Avanzados
Buscador en tiempo real por nombre o código.

Filtros combinables por Categoría y Estado de Stock.

Los filtros se mantienen activos al navegar entre las páginas de la paginación.

4. Gestión Auxiliar
Usuarios: ABM (Alta, Baja, Modificación) de usuarios administradores sin salir del dashboard.

Categorías: Gestión dinámica de categorías. Si se borra una categoría, sus productos pasan a "Sin categoría" automáticamente para no perder datos.

5. Reportes y Exportación
Generación de reportes en formato Excel (.xls) con un solo clic, incluyendo fecha de generación y usuario responsable.

⚙️ Arquitectura y Flujo de Funcionamiento
Autenticación:

El sistema protege todas las rutas. Si verificarLogin() detecta que no hay sesión activa, redirige a login.php.

El acceso se valida contra la base de datos MySQL.

Interfaz de Usuario (Layout):

Diseño Side-by-Side: Barra lateral izquierda fija para navegación principal y perfil de usuario.

Encabezado superior limpio con botones de acción rápida unificados visualmente.

Notificaciones Toast: Mensajes emergentes (no intrusivos) en la esquina inferior derecha para confirmar acciones (éxito/error).

Comunicación Cliente-Servidor:

Síncrona: Para el flujo principal (Login, Filtros, Paginación, Guardar Producto) se utilizan recargas de página optimizadas.
