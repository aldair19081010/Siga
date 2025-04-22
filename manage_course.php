<?php
include 'db_connect.php';
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM courses WHERE id = " . $_GET['id']);
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_array() as $k => $val) {
            $$k = $val;
        }
    } else {
        echo "<script>alert('Curso no encontrado.'); location.replace('courses.php');</script>";
        exit;
    }
}
?>
<div class="container-fluid">
    <form action="" id="manage-course">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="row">
            <div class="col-lg-6 border-right">
                <h5><b>Conceptos de pago</b></h5>
                <hr>
                <div id="msg" class="form-group"></div>
                <div class="form-group">
                    <label for="" class="control-label">Concepto</label>
                    <input type="text" class="form-control" name="course" value="<?php echo isset($course) ? $course : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="level" class="control-label">Nivel</label>
                    <select class="form-control" name="level" id="level" required>
                        <option value="" disabled>Seleccione un nivel</option>
                        <option value="Inicial" <?php echo (isset($level) && $level == 'Inicial') ? 'selected' : '' ?>>Inicial</option>
                        <option value="Primaria" <?php echo (isset($level) && $level == 'Primaria') ? 'selected' : '' ?>>Primaria</option>
                        <option value="Secundaria" <?php echo (isset($level) && $level == 'Secundaria') ? 'selected' : '' ?>>Secundaria</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Descripción</label>
                    <textarea name="description" id="" cols="30" rows="4" class="form-control" required><?php echo isset($description) ? $description : '' ?></textarea>
                </div>
            </div>
            <div class="col-lg-6">
                <h5><b>Información de los Pagos</b></h5>
                <hr>
                <div class="form-group">
                    <label for="" class="control-label">Monto</label>
                    <input type="number" step="any" min="0" id="amount" class="form-control text-right">
                </div>
                <div class="form-group pt-1">
                    <label for="" class="control-label">&nbsp;</label>
                    <button class="btn btn-primary btn-sm" type="button" id="add_fee">Agregar a la Lista</button>
                </div>
                <hr>
                <table class="table table-condensed" id="fee-list">
                    <thead>
                        <tr>
                            <th width="5%"></th>
                            <th width="50%">Tipo</th>
                            <th width="45%">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($id)) :
                            $fees = $conn->query("SELECT * FROM fees WHERE course_id = $id");
                            $total = 0;
                            while ($row = $fees->fetch_assoc()) :
                                $total += $row['amount'];
                        ?>
                                <tr>
                                    <td class="text-center"><button class="btn-sm btn-outline-danger" type="button" onclick="rem_list($(this))"><i class="fa fa-times"></i></button></td>
                                    <td>
                                        <input type="hidden" name="fid[]" value="<?php echo $row['id'] ?>">
                                        <input type="hidden" name="type[]" value="<?php echo $row['description'] ?>">
                                        <p><small><b class="ftype"><?php echo $row['description'] ?></b></small></p>
                                    </td>
                                    <td>
                                        <input type="hidden" name="amount[]" value="<?php echo $row['amount'] ?>">
                                        <p class="text-right"><small><b class="famount"><?php echo number_format($row['amount'], 2) ?></b></small></p>
                                    </td>
                                </tr>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-center">Total</th>
                            <th class="text-right">
                                <input type="hidden" name="total_amount" value="<?php echo isset($total) ? $total : 0 ?>">
                                <span class="tamount"><?php echo isset($total) ? number_format($total, 2) : '0.00' ?></span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </form>
</div>
<div id="fee_clone" style="display: none">
    <table>
        <tr>
            <td class="text-center"><button class="btn-sm btn-outline-danger" type="button" onclick="rem_list($(this))"><i class="fa fa-times"></i></button></td>
            <td>
                <input type="hidden" name="fid[]">
                <input type="hidden" name="type[]">
                <p><small><b class="ftype"></b></small></p>
            </td>
            <td>
                <input type="hidden" name="amount[]">
                <p class="text-right"><small><b class="famount"></b></small></p>
            </td>
        </tr>
    </table>
</div>

<script>
    $('#manage-course').on('reset', function() {
        $('#msg').html('');
        $('input:hidden').val('');
    });

    $('#add_fee').click(function() {
        var amount = $('#amount').val();
        if (amount == '') {
            alert_toast("Complete el campo de monto.", 'warning');
            return false;
        }
        var tr = $('#fee_clone tr').clone();
        tr.find('[name="amount[]"]').val(amount);
        tr.find('.famount').text(parseFloat(amount).toLocaleString('en-US'));
        $('#fee-list tbody').append(tr);
        $('#amount').val('');
        calculate_total();
    });

    $('#manage-course').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        if ($('#fee-list tbody').find('[name="fid[]"]').length <= 0) {
            alert_toast("Inserte al menos 1 fila en la tabla de tarifas", 'danger');
            end_load();
            return false;
        }
        $.ajax({
            url: 'ajax.php?action=save_course',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp) {
                try {
                    if (typeof resp === 'string') {
                        resp = JSON.parse(resp); // Asegurarse de que la respuesta sea JSON válida
                    }
                    if (resp.status == 1) {
                        alert_toast(resp.message, 'success'); // Mostrar mensaje de éxito
                        setTimeout(function() {
                            location.reload(); // Recargar la página después de guardar
                        }, 500);
                    } else if (resp.status == 2) {
                        $('#msg').html('<div class="alert alert-danger mx-2">' + resp.message + '</div>');
                        end_load();
                    } else {
                        alert_toast(resp.message, 'danger');
                        end_load();
                    }
                } catch (err) {
                    console.error("Error al procesar la respuesta del servidor:", err);
                    alert_toast("Error inesperado. Intente nuevamente más tarde.", 'danger');
                    end_load();
                }
            },
            error: function(err) {
                console.error("Error en la solicitud AJAX:", err);
                alert_toast("Error en el servidor. Intente nuevamente más tarde.", 'danger');
                end_load();
            }
        });
    });

    function calculate_total() {
        var total = 0;
        $('#fee-list tbody').find('[name="amount[]"]').each(function() {
            total += parseFloat($(this).val());
        });
        $('#fee-list tfoot').find('.tamount').text(parseFloat(total).toLocaleString('en-US'));
        $('#fee-list tfoot').find('[name="total_amount"]').val(total);
    }

    function rem_list(_this) {
        _this.closest('tr').remove();
        calculate_total();
    }
</script>