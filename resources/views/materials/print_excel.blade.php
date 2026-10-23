<html>
<head>
    <meta charset="UTF-8">
    <title>REPORTE DE INVENTARIO</title>
</head>
<body>
    <table border="1" width="100%" align="center" style="border-collapse: collapse;">
        <tr>
            <td colspan="7" align="center" bgcolor="#CCCCCC">
                <strong>DEFENSORÍA DEL PUEBLO - DELEGACIÓN DEFENSORIAL DEPARTAMENTAL ORURO</strong>
            </td>
        </tr>
        <tr>
            <td colspan="7" align="center">
                REPORTE GENERAL DE BALANCE DE INVENTARIO Y STOCK DE MATERIALES
            </td>
        </tr>
        <tr>
            <td colspan="7">
                FECHA DE GENERACIÓN: {{ date('d/m/Y H:i:s') }}
            </td>
        </tr>
        <tr>
            <th style="background-color: #e5e7eb; border: 1px solid black; padding: 5px;" align="center"><strong>N°</strong></th>
            <th style="background-color: #e5e7eb; border: 1px solid black; padding: 5px;" align="center"><strong>CÓDIGO DE MATERIAL</strong></th>
            <th style="background-color: #e5e7eb; border: 1px solid black; padding: 5px;" align="center"><strong>DESCRIPCIÓN / PRODUCTO</strong></th>
            <th style="background-color: #e5e7eb; border: 1px solid black; padding: 5px;" align="center"><strong>CATEGORÍA</strong></th>
            <th style="background-color: #e5e7eb; border: 1px solid black; padding: 5px;" align="center"><strong>STOCK ACTUAL</strong></th>
            <th style="background-color: #e5e7eb; border: 1px solid black; padding: 5px;" align="center"><strong>STOCK MÍNIMO DE SEGURIDAD</strong></th>
            <th style="background-color: #e5e7eb; border: 1px solid black; padding: 5px;" align="center"><strong>ESTADO DE ALERTA DE STOCK</strong></th>
        </tr>
        @foreach($materiales as $index => $material)
        <tr>
            <td class="col-nro" align="center">{{ $index + 1 }}</td>
            <td class="col-codigo" align="center">{{ strtoupper($material->codigo) }}</td>
            <td class="col-detalle">{{ strtoupper($material->nombre) }}</td>
            <td class="col-categoria">{{ strtoupper($material->categoria->nombre ?? 'SIN CATEGORÍA') }}</td>
            <td class="col-stock" align="center">{{ $material->stock }}</td>
            <td class="col-minimo" align="center">{{ $material->stock_minimo }}</td>
            <td class="col-alerta" align="center">
                @if($material->stock <= $material->stock_minimo)
                    ALERTA: STOCK CRÍTICO / REAPROVISIONAR
                @else
                    STOCK SUFICIENTE EN ALMACÉN
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</body>
</html>
