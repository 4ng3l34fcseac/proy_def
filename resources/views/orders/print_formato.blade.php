<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLICITUD DE MATERIALES - {{ $pedido->codigo }}</title>
    <style>
        @page {
            size: Letter;
            margin: 12mm 10mm 12mm 10mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Arial", "Helvetica", sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
        }
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            text-align: left;
        }
        .text-uppercase {
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .fw-bold {
            font-weight: bold;
        }
        .header-table td {
            padding: 6px 8px;
        }
        .logo-cell {
            width: 18%;
            text-align: center;
            vertical-align: middle;
        }
        .title-cell {
            width: 56%;
            text-align: center;
            vertical-align: middle;
        }
        .title-text {
            font-weight: bold;
            font-size: 11pt;
            line-height: 1.3;
            text-transform: uppercase;
        }
        .control-table {
            width: 26%;
            font-size: 8pt;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .control-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 8pt;
        }
        .control-label {
            font-weight: bold;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
            margin-top: 10px;
            margin-bottom: 4px;
            background: #e0e0e0;
            padding: 3px 6px;
            border: 1px solid #000;
        }
        .datos-grid {
            margin-top: 6px;
        }
        .datos-grid table {
            border: 2px solid #000;
        }
        .datos-grid td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9pt;
            vertical-align: middle;
        }
        .label-cell {
            font-weight: bold;
            text-transform: uppercase;
            background: #f0f0f0;
            width: 22%;
        }
        .value-cell {
            width: 28%;
        }
        .materiales-table {
            margin-top: 10px;
        }
        .materiales-table thead th {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
            background: #e8e8e8;
            text-align: center;
            padding: 5px 4px;
        }
        .materiales-table .col-numero {
            width: 6%;
            text-align: center;
        }
        .materiales-table .col-cantidad {
            width: 18%;
            text-align: center;
        }
        .materiales-table .col-unidad {
            width: 22%;
            text-align: center;
        }
        .materiales-table .col-detalle {
            width: 54%;
            text-align: left;
        }
        .materiales-table tbody td {
            text-align: center;
            font-size: 9pt;
            padding: 5px 6px;
            vertical-align: top;
        }
        .materiales-table tbody td.col-detalle {
            text-align: left;
        }
        .justificacion-box {
            margin-top: 10px;
            border: 2px solid #000;
            padding: 8px 10px;
            background: #fafafa;
            min-height: 60px;
        }
        .justificacion-box .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
            margin-bottom: 4px;
        }
        .justificacion-box .texto {
            font-size: 9pt;
            text-transform: uppercase;
            line-height: 1.4;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .firmas-section {
            margin-top: 12px;
        }
        .firmas-table {
            border: 2px solid #000;
        }
        .firmas-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: center;
            vertical-align: bottom;
            height: 70px;
            font-size: 8pt;
        }
        .firma-titulo {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            margin-bottom: 4px;
        }
        .firma-linea {
            border-bottom: 1px dotted #000;
            margin-top: 20px;
            padding-bottom: 2px;
        }
        .texto-justificacion-vacio {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        @php
            $meses = [
                1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
            ];
            $dia = $pedido->created_at->format('d');
            $mes = $meses[(int)$pedido->created_at->format('m')];
            $anio = $pedido->created_at->format('Y');
        @endphp

        <table border="1" style="width: 100%; border-collapse: collapse; text-align: left; font-family: sans-serif; font-size: 11px; margin-bottom: 20px;">
            <tr>
                <td rowspan="3" style="width: 22%; text-align: center; padding: 8px; border: 1px solid black; vertical-align: middle;">
                    <img src="{{ asset('img/logo.jpg') }}" style="max-width: 65px; height: auto;" alt="Logo">
                    <div style="font-size: 8px; font-weight: bold; margin-top: 4px; line-height: 9px;">DEFENSORÍA DEL PUEBLO<br><span style="font-size: 6px; font-weight: normal;">ESTADO PLURINACIONAL DE BOLIVIA</span></div>
                </td>
                <td style="width: 53%; border: 1px solid black; padding: 5px; font-weight: bold; text-align: center; font-size: 12px;">DEFENSORIA DEL PUEBLO</td>
                <td style="width: 12%; font-weight: bold; border: 1px solid black; padding: 5px;">Código:</td>
                <td style="width: 13%; border: 1px solid black; padding: 5px;">DGAF/UADM-R-23</td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 5px; height: 35px; vertical-align: top;">
                    <span style="font-size: 9px; color: #4b5563; font-weight: bold;">Tipo de documento:</span><br>
                    <span style="font-weight: bold; padding-left: 2px; font-size: 11px;">REGISTRO</span>
                </td>
                <td style="font-weight: bold; border: 1px solid black; padding: 5px; vertical-align: middle;">Vigente desde:</td>
                <td style="border: 1px solid black; padding: 5px; vertical-align: middle;">24/6/2025</td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 5px; height: 35px; vertical-align: top;">
                    <span style="font-size: 9px; color: #4b5563; font-weight: bold;">Título:</span><br>
                    <span style="font-weight: bold; padding-left: 2px; font-size: 11px;">SOLICITUD DE MATERIALES</span>
                </td>
                <td style="font-weight: bold; border: 1px solid black; padding: 5px; vertical-align: middle;">Versión:<br><br>Página:</td>
                <td style="border: 1px solid black; padding: 5px; vertical-align: middle;">1<br><br>1 de 1</td>
            </tr>
        </table>

        <div style="text-align: center; font-family: sans-serif; font-size: 16px; font-weight: bold; margin-top: 10px; margin-bottom: 15px; letter-spacing: 1px;">
            SOLICITUD DE MATERIALES
        </div>

        <div style="font-family: sans-serif; font-size: 11px; line-height: 18px; margin-bottom: 15px;">
            <strong>FECHA:</strong> {{ $dia }} DE {{ $mes }} DE {{ $anio }}<br>
            <strong>UNIDAD SOLICITANTE:</strong> DELEGACIÓN DEFENSORIAL DEPARTAMENTAL ORURO
        </div>

        <div class="materiales-section">
            <table class="materiales-table">
                <thead>
                    <tr>
                        <th class="col-numero">ITEM</th>
                        <th class="col-cantidad">CANTIDAD NUMERAL</th>
                        <th class="col-unidad">UNIDAD DE MEDIDA</th>
                        <th class="col-detalle">DETALLE DEL MATERIAL (DESCRIPCIÓN)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->materiales as $index => $material)
                    <tr>
                        <td class="col-numero">{{ $index + 1 }}</td>
                        <td class="col-cantidad">{{ $material->cantidad }}</td>
                        <td class="col-unidad">{{ strtoupper($material->unidad_medida) }}</td>
                        <td class="col-detalle">{{ strtoupper($material->nombre) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="justificacion-box">
            <div class="label">JUSTIFICACIÓN:</div>
            <div class="texto">
                {{ $pedido->justificacion ? strtoupper($pedido->justificacion) : '' }}
            </div>
        </div>

        <div class="firmas-section">
            <table class="firmas-table">
                <tr>
                    <td style="width: 28%;">
                        <div class="firma-titulo">FECHA DE RECEPCIÓN / SELLO DE ENTRADA</div>
                        <div style="border-bottom: 1px dotted #000; margin-top: 35px;">&nbsp;</div>
                    </td>
                    <td style="width: 44%;">
                        <div class="firma-titulo">JEFE DE UNIDAD SOLICITANTE - SELLO Y FIRMA</div>
                        <div class="firma-linea" style="margin-top: 30px;">&nbsp;</div>
                    </td>
                    <td style="width: 28%;">
                        <div class="firma-titulo">JEFE DE LA UNIDAD ADMINISTRATIVA - ENTREGUE CONFORME - SELLO Y FIRMA</div>
                        <div class="firma-linea" style="margin-top: 30px;">&nbsp;</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if(isset($autoPrint))
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
</body>
</html>
