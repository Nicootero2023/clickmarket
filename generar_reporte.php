<?php
require_once 'config.php';
verificarLogin();

// Obtener todos los productos
$pdo = conectarDB();
$stmt = $pdo->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     ORDER BY p.codigo");
$productos = $stmt->fetchAll();

// Obtener estadi­sticas
$stats = obtenerEstadisticas();

// Configurar headers para descargar archivo Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="Reporte_Inventario_' . date('Y-m-d_H-i-s') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Usar BOM para UTF-8
echo "\xEF\xBB\xBF";
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left;
            white-space: nowrap;
        }
        th { 
            background-color: #667eea; 
            color: white; 
            font-weight: bold; 
        }
        .header-section { 
            margin-bottom: 20px; 
            font-size: 14px;
        }
        .header-title { 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 10px;
        }
        .stats-table { 
            margin: 20px 0; 
            max-width: 600px;
        }
        .en-stock { background-color: #d4edda; }
        .stock-bajo { background-color: #fff3cd; }
        .sin-stock { background-color: #f8d7da; }
        
        /* Anchos de columna especificos */
        .col-codigo { width: 100px; }
        .col-nombre { width: 250px; }
        .col-precio { width: 120px; }
        .col-stock { width: 80px; }
        .col-categoria { width: 150px; }
        .col-estado { width: 120px; }
        .col-fecha { width: 150px; }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="header-title">CLICKMARKET - REPORTE DE INVENTARIO</div>
        <div><strong>Fecha de generacion:</strong> <?php echo date('d/m/Y H:i:s'); ?></div>
        <div><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'N/A'); ?></div>
    </div>
    
    <div style="margin: 20px 0;">
        <h3>Resumen del Inventario</h3>
        <table class="stats-table">
            <tr>
                <th>Total de Productos</th>
                <th>En Stock</th>
                <th>Stock Bajo</th>
                <th>Sin Stock</th>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;"><?php echo $stats['total']; ?></td>
                <td style="text-align: center; font-weight: bold; color: #28a745;"><?php echo $stats['en_stock']; ?></td>
                <td style="text-align: center; font-weight: bold; color: #ffc107;"><?php echo $stats['stock_bajo']; ?></td>
                <td style="text-align: center; font-weight: bold; color: #dc3545;"><?php echo $stats['sin_stock']; ?></td>
            </tr>
        </table>
    </div>
    
    <h3>Detalle de Productos</h3>
    <table>
        <thead>
            <tr>
                <th class="col-codigo">Codigo</th>
                <th class="col-nombre">Nombre del Producto</th>
                <th class="col-precio">Precio (ARS)</th>
                <th class="col-stock">Stock</th>
                <th class="col-categoria">Categoria</th>
                <th class="col-estado">Estado</th>
                <th class="col-fecha">Fecha de Creacion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
                <?php 
                    $estadoClass = '';
                    $estadoTexto = obtenerTextoEstado($producto['stock']);
                    
                    if ($producto['stock'] == 0) {
                        $estadoClass = 'sin-stock';
                    } elseif ($producto['stock'] < 5) {
                        $estadoClass = 'stock-bajo';
                    } else {
                        $estadoClass = 'en-stock';
                    }
                ?>
                <tr class="<?php echo $estadoClass; ?>">
                    <td class="col-codigo"><?php echo htmlspecialchars($producto['codigo']); ?></td>
                    <td class="col-nombre"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td class="col-precio" style="text-align: right;">$ <?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                    <td class="col-stock" style="text-align: center;"><?php echo $producto['stock']; ?></td>
                    <td class="col-categoria"><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categorÃ­a'); ?></td>
                    <td class="col-estado" style="text-align: center; font-weight: bold;"><?php echo $estadoTexto; ?></td>
                    <td class="col-fecha"><?php echo date('d/m/Y H:i', strtotime($producto['fecha_creacion'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <br><br>
    <div style="text-align: center; color: #666; font-size: 12px;">
        <em>Reporte generado automaticamente por Clickmarket</em>
    </div>
</body>
</html>