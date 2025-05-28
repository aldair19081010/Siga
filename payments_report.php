<?php
include 'db_connect.php';

// Establecer valores predeterminados y procesar parámetros
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'month';
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
?>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card_body">
                <div class="row justify-content-center pt-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filter_type">Tipo de filtro</label>
                            <select name="filter_type" id="filter_type" class="form-control">
                                <option value="month" <?php echo $filter_type == 'month' ? 'selected' : '' ?>>Por Mes</option>
                                <option value="date" <?php echo $filter_type == 'date' ? 'selected' : '' ?>>Por Día</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4" id="month_filter" style="<?php echo $filter_type == 'date' ? 'display:none' : '' ?>">
                        <div class="form-group">
                            <label for="month">Mes</label>
                            <input type="month" name="month" id="month" value="<?php echo $month ?>" class="form-control">
                        </div>
                    </div>
                    
                    <div class="col-md-4" id="date_filter" style="<?php echo $filter_type == 'month' ? 'display:none' : '' ?>">
                        <div class="form-group">
                            <label for="date">Fecha</label>
                            <input type="date" name="date" id="date" value="<?php echo $date ?>" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="student_id">Estudiante</label>
                            <select name="student_id" id="student_id" class="form-control">
                                <option value="">Todos</option>
                                <?php
                                $students = $conn->query("SELECT id, name, id_no FROM student ORDER BY name ASC");
                                while ($stu = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $stu['id']; ?>" <?php echo (isset($_GET['student_id']) && $_GET['student_id'] == $stu['id']) ? 'selected' : '' ?>>
                                        <?php echo $stu['id_no'] . ' - ' . ucwords($stu['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group" style="margin-top: 31px;">
                            <button class="btn btn-primary btn-block" id="filter_btn">Filtrar</button>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-md-12">
                    <table class="table table-bordered" id='report-list'>
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="">Fecha</th>
                                <th class="">ID</th>
                                <th class="">Concepto de Pago</th>
                                <th class="">Nombre</th>
                                <th class="">Monto Pagado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $total = 0;
                            
                            // Construir la consulta SQL según el tipo de filtro
                            $where = "";
                            
                            if ($filter_type == 'month' && !empty($month)) {
                                $where = " WHERE DATE_FORMAT(p.date_created, '%Y-%m') = '$month' ";
                                $period_label = date("F Y", strtotime($month . "-01"));
                            } elseif ($filter_type == 'date' && !empty($date)) {
                                $where = " WHERE DATE(p.date_created) = '$date' ";
                                $period_label = date("d F, Y", strtotime($date));
                            }

                            // Filtro SQL por estudiante
                            if (!empty($student_id)) {
                                $where .= (empty($where) ? ' WHERE ' : ' AND ') . " s.id = '" . $conn->real_escape_string($student_id) . "' ";
                            }
                            
                            $sql = "SELECT p.*, s.name as sname, s.id_no, ef.id as ef_id, ef.total_fee, c.course as concept_name, 
                                    pm.name as payment_method
                                    FROM payments p 
                                    INNER JOIN student_ef_list ef ON ef.id = p.ef_id 
                                    INNER JOIN student s ON s.id = ef.student_id 
                                    INNER JOIN courses c ON c.id = ef.course_id 
                                    LEFT JOIN payment_methods pm ON pm.id = p.payment_method_id
                                    $where
                                    ORDER BY p.date_created DESC";
                            
                            $payments = $conn->query($sql);
                            
                            if ($payments && $payments->num_rows > 0) :
                                while ($row = $payments->fetch_assoc()) :
                                    $total += $row['amount'];
                            ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td>
                                            <p><?php echo date("d M Y, h:i A", strtotime($row['date_created'])) ?></p>
                                        </td>
                                        <td>
                                            <p><?php echo $row['id_no'] ?></p>
                                        </td>
                                        <td>
                                            <p><?php echo $row['concept_name'] ?></p>
                                        </td>
                                        <td>
                                            <p><?php echo ucwords($row['sname']) ?></p>
                                        </td>
                                        <td class="text-right">
                                            <p><?php echo number_format($row['amount'], 2) ?></p>
                                        </td>
                                        <td>
                                            <p><?php echo $row['remarks'] ?></p>
                                            <small class="text-muted">Método: <?php echo $row['payment_method'] ? $row['payment_method'] : 'No especificado' ?></small>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else :
                                ?>
                                <tr>
                                    <th class="text-center" colspan="7">Sin datos que mostrar</th>
                                </tr>
                            <?php
                            endif;
                            ?>
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total</th>
                                <th class="text-right"><?php echo number_format($total, 2) ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                    <hr>
                    <div class="col-md-12 mb-4">
                        <center>
                            <button class="btn btn-success col-sm-3 col-md-2" type="button" id="print"><i class="fa fa-print"></i> Imprimir</button>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<noscript>
    <style>
        table#report-list {
            width: 100%;
            border-collapse: collapse
        }

        table#report-list td,
        table#report-list th {
            border: 1px solid
        }

        p {
            margin: unset;
        }

        .text-center {
            text-align: center
        }

        .text-right {
            text-align: right
        }
    </style>
</noscript>

<script>
    $(document).ready(function() {
        // Cambiar visibilidad de filtros según tipo seleccionado
        $('#filter_type').change(function() {
            if ($(this).val() == 'month') {
                $('#month_filter').show();
                $('#date_filter').hide();
            } else {
                $('#month_filter').hide();
                $('#date_filter').show();
            }
        });
        
        // Manejar botón de filtro
        $('#filter_btn').click(function() {
            let filter_type = $('#filter_type').val();
            let params = 'filter_type=' + filter_type;
            
            if (filter_type == 'month') {
                params += '&month=' + $('#month').val();
            } else {
                params += '&date=' + $('#date').val();
            }
            let student_id = $('#student_id').val();
            if (student_id) {
                params += '&student_id=' + student_id;
            }
            location.replace('index.php?page=payments_report&' + params);
        });
        
        // Imprimir reporte
        $('#print').click(function() {
            var _c = $('#report-list').clone();
            var ns = $('noscript').clone();
            ns.append(_c);
            var nw = window.open('', '_blank', 'width=900,height=600');
            
            let title = '';
            let filter_type = '<?php echo $filter_type; ?>';
            
            if (filter_type == 'month') {
                title = 'Reporte de Pagos del Mes de <?php echo isset($period_label) ? $period_label : date("F Y") ?>';
            } else {
                title = 'Reporte de Pagos del Día <?php echo isset($period_label) ? $period_label : date("d F, Y") ?>';
            }
            
            nw.document.write('<p class="text-center"><b>' + title + '</b></p>');
            nw.document.write(ns.html());
            nw.document.close();
            nw.print();
            setTimeout(() => {
                nw.close();
            }, 500);
        });

        // Hacer el select de estudiantes con búsqueda (select2)
        $('#student_id').select2({
            width: '100%',
            placeholder: 'Buscar estudiante...',
            allowClear: true
        });
    });
</script>