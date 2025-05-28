<?php include 'db_connect.php'; ?>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <b>Reportes de Deudas</b>
            </div>
            <div class="card-body">
                <form id="filter-form">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="type">Tipo de Reporte</label>
                            <select id="type" class="form-control">
                                <option value="general">General</option>
                                <option value="alumno">Por Alumno</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="student-select-container" style="display: none;">
                            <label for="student_id">Alumno</label>
                            <select id="student_id" class="form-control">
                                <option value="">Seleccione un alumno</option>
                                <?php
                                $students = $conn->query("SELECT id, name FROM student ORDER BY name ASC");
                                while ($row = $students->fetch_assoc()):
                                ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="nivel">Nivel</label>
                            <select id="nivel" class="form-control">
                                <option value="">Todos los niveles</option>
                                <option value="Inicial">Inicial</option>
                                <option value="Primaria">Primaria</option>
                                <option value="Secundaria">Secundaria</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="grado">Grado</label>
                            <select id="grado" class="form-control">
                                <option value="">Todos los grados</option>
                                <?php 
                                    $grados = ["1°", "2°", "3°", "4°", "5°", "6°"];
                                    foreach($grados as $grado): 
                                ?>
                                <option value="<?php echo $grado; ?>"><?php echo $grado; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label for="seccion">Sección</label>
                            <select id="seccion" class="form-control">
                                <option value="">Todas las secciones</option>
                                <?php 
                                    $secciones = ["A", "B", "C", "D", "U"];
                                    foreach($secciones as $seccion): 
                                ?>
                                <option value="<?php echo $seccion; ?>"><?php echo $seccion; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="concepto">Concepto de Pago</label>
                            <select id="concepto" class="form-control">
                                <option value="">Todos los conceptos</option>
                                <?php
                                $conceptos = $conn->query("SELECT id, course FROM courses ORDER BY course ASC");
                                while ($row = $conceptos->fetch_assoc()):
                                ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['course']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date">Fecha Inicio</label>
                            <input type="date" id="start_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date">Fecha Fin</label>
                            <input type="date" id="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-center">
                            <button type="button" id="generate-report" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Generar Reporte
                            </button>
                            <button type="button" id="print-report" class="btn btn-success ml-2">
                                <i class="fa fa-print"></i> Imprimir Reporte
                            </button>
                        </div>
                    </div>
                </form>
                <hr>
                <div id="report-container">
                    <table class="table table-bordered table-hover" id="debt_reports_table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Alumno</th>
                                <th>Concepto</th>
                                <th>Monto Total</th>
                                <th>Pagado</th>
                                <th>Deuda</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Mostrar/ocultar selector de estudiante
    $('#type').change(function() {
        if ($(this).val() === 'alumno') {
            $('#student-select-container').show();
        } else {
            $('#student-select-container').hide();
            $('#student_id').val('');
        }
    });

    // Generar el reporte
    $('#generate-report').click(function() {
        generateReport();
    });

    // Imprimir el reporte
    $('#print-report').click(function() {
        printReport();
    });

    function generateReport() {
        const type = $('#type').val();
        const student_id = $('#student_id').val();
        const nivel = $('#nivel').val();
        const grado = $('#grado').val();
        const seccion = $('#seccion').val();
        const concepto = $('#concepto').val();
        const start_date = $('#start_date').val();
        const end_date = $('#end_date').val();

        $.ajax({
            url: 'api/debt_reports.php',
            method: 'GET',
            data: { 
                type, 
                student_id, 
                nivel, 
                grado, 
                seccion, 
                concepto,
                start_date, 
                end_date 
            },
            success: function(response) {
                if (response.status === 'ok') {
                    const tbody = $('#debt_reports_table tbody');
                    tbody.empty();
                    
                    if (response.data.length === 0) {
                        tbody.append(`
                            <tr>
                                <td colspan="6" class="text-center">No se encontraron resultados</td>
                            </tr>
                        `);
                    } else {
                        response.data.forEach((row, index) => {
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${row.student_name}</td>
                                    <td>${row.concepto}</td>
                                    <td>${parseFloat(row.total_fee).toFixed(2)}</td>
                                    <td>${parseFloat(row.pagado).toFixed(2)}</td>
                                    <td>${parseFloat(row.deuda).toFixed(2)}</td>
                                </tr>
                            `);
                        });
                    }
                } else {
                    alert('Error al generar el reporte: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud:", xhr.responseText);
                alert('Error al comunicarse con el servidor');
            }
        });
    }

    function printReport() {
        // Crear una ventana de impresión
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        
        // Encabezado del reporte
        const type = $('#type').val() === 'alumno' ? 'Por Alumno' : 'General';
        const nivel = $('#nivel').val() || 'Todos';
        const grado = $('#grado').val() || 'Todos';
        const seccion = $('#seccion').val() || 'Todas';
        const start_date = $('#start_date').val() ? new Date($('#start_date').val()).toLocaleDateString() : 'N/A';
        const end_date = $('#end_date').val() ? new Date($('#end_date').val()).toLocaleDateString() : 'N/A';
        
        // Contenido HTML para imprimir
        let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Reporte de Deudas</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .filters { margin-bottom: 20px; }
                    .filters p { margin: 5px 0; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Reporte de Deudas</h1>
                </div>
                <div class="filters">
                    <p><strong>Tipo:</strong> ${type}</p>
                    <p><strong>Nivel:</strong> ${nivel}</p>
                    <p><strong>Grado:</strong> ${grado}</p>
                    <p><strong>Sección:</strong> ${seccion}</p>
                    <p><strong>Fecha Inicio:</strong> ${start_date}</p>
                    <p><strong>Fecha Fin:</strong> ${end_date}</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Alumno</th>
                            <th>Concepto</th>
                            <th>Monto Total</th>
                            <th>Pagado</th>
                            <th>Deuda</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Agregar filas de la tabla al contenido de impresión
        $('#debt_reports_table tbody tr').each(function(index) {
            printContent += '<tr>';
            $(this).find('td').each(function() {
                printContent += '<td>' + $(this).text() + '</td>';
            });
            printContent += '</tr>';
        });
        
        // Cerrar el contenido HTML
        printContent += `
                    </tbody>
                </table>
            </body>
            </html>
        `;
        
        // Escribir en la ventana de impresión
        printWindow.document.open();
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Imprimir después de que se cargue el contenido
        printWindow.onload = function() {
            printWindow.print();
        };
    }
});
</script>
