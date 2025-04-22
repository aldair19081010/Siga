<?php include 'db_connect.php'; ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">

			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Pagos</b>
						<span class="float:right"><a class="btn btn-primary col-md-1 col-sm-6 float-right" href="javascript:void(0)" id="new_payment">
								<i class="fa fa-plus"></i> Pago
							</a></span>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="">Fecha</th>
									<th class="">Dni</th>
									<th class="">N° Boleta</th>
									<th class="">Nombre</th>
									<th class="">Monto Pagado</th>
									<th class="">Concepto</th>
									<th class="text-center">Acción</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1; // Inicializar contador para numeración
								$payments = $conn->query("
									SELECT p.*, p.receipt_no, s.name as sname, s.id_no, c.course as concept_name 
									FROM payments p 
									INNER JOIN student_ef_list ef ON ef.id = p.ef_id 
									INNER JOIN student s ON s.id = ef.student_id 
									INNER JOIN courses c ON c.id = ef.course_id 
									ORDER BY unix_timestamp(p.date_created) DESC
								");
								if ($payments->num_rows > 0) :
									while ($row = $payments->fetch_assoc()) :
								?>
										<tr>
											<td class="text-center"><?php echo $i++ ?></td> <!-- Mostrar numeración -->
											<td>
												<p><?php echo date("M d,Y H:i A", strtotime($row['date_created'])) ?></p>
											</td>
											<td>
												<p><?php echo $row['id_no'] ?></p>
											</td>
											<td>
												<p><?php echo $row['receipt_no'] ?></p> <!-- Mostrar el número de boleta -->
											</td>
											<td>
												<p><?php echo ucwords($row['sname']) ?></p>
											</td>
											<td class="text-right">
												<p><?php echo number_format($row['amount'], 2) ?></p>
											</td>
												<td>
													<p><?php echo $row['concept_name'] ?></p>
												</td>
											<td class="text-center">
												<button class="btn btn-primary view_payment" type="button" data-id="<?php echo $row['id'] ?>" data-ef_id="<?php echo $row['ef_id'] ?>"><i class="fa fa-eye"></i></button>
												<button class="btn btn-info edit_payment" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
												<button class="btn btn-danger delete_payment" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
											</td>
										</tr>
									<?php
									endwhile;
								else :
									?>
									<tr>
										<th class="text-center" colspan="8">Sin datos que mostrar.</th>
									</tr>
								<?php
								endif;

								?>
							</tbody>

						</table>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	td {
		vertical-align: middle !important;
	}

	td p {
		margin: unset
	}

	img {
		max-width: 100px;
		max-height: :150px;
	}
</style>

<script>
	$(document).ready(function() {
		$('table').dataTable();
	});

	$('#new_payment').click(function() {
		uni_modal("Nuevo Pago", "manage_payment.php", "mid-large");
	});

	$('.view_payment').click(function() {
		uni_modal("Información de Pago", "view_payment.php?ef_id=" + $(this).attr('data-ef_id') + "&pid=" + $(this).attr('data-id'), "mid-large");
	});

	$('.edit_payment').click(function() {
		uni_modal("Gestionar Pago", "manage_payment.php?id=" + $(this).attr('data-id'), "mid-large");
	});

	$('.delete_payment').click(function() {
		_conf("¿Deseas eliminar este pago?", "delete_payment", [$(this).attr('data-id')]);
	});

	function delete_payment($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_payment',
			method: 'POST',
			data: { id: $id },
			success: function(resp) {
				try {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp); // Asegurarse de que la respuesta sea JSON válida
					}
					if (resp.status == 1) {
						alert_toast("Pago eliminado exitosamente.", 'success');
						setTimeout(function() {
							location.reload(); // Recargar la página después de eliminar
						}, 500);
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
	}
</script>

<?php
function save_payment() {
    extract($_POST);
    $data = "";
    foreach ($_POST as $k => $v) {
        if (!in_array($k, array('id')) && !is_numeric($k)) {
            if ($k == 'amount') {
                $v = str_replace(',', '', $v);
            }
            if (empty($data)) {
                $data .= " $k='$v' ";
            } else {
                $data .= ", $k='$v' ";
            }
        }
    }
    if (empty($id)) {
        $save = $this->db->query("INSERT INTO payments set $data");
        if ($save)
            $id = $this->db->insert_id;
    } else {
        $save = $this->db->query("UPDATE payments set $data where id = $id");
    }
    if ($save)
        return json_encode(array('ef_id' => $ef_id, 'pid' => $id, 'status' => 1));
}
?>