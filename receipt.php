<?php
include 'db_connect.php';
$fees = $conn->query("SELECT ef.*,s.name as sname,s.id_no,s.school_id,concat(c.course,' - ',c.level) as `class` FROM student_ef_list ef inner join student s on s.id = ef.student_id inner join courses c on c.id = ef.course_id  where ef.id = {$_GET['ef_id']}");
foreach ($fees->fetch_array() as $k => $v) {
	$$k = $v;
}

// Obtener información del colegio
$school_query = $conn->query("SELECT * FROM schools WHERE id = $school_id");
$school_data = $school_query->fetch_assoc();
$school_name = $school_data['name'] ?? 'Colegio';
$school_address = $school_data['address'] ?? '';
$school_contact = $school_data['contact'] ?? '';

$payments = $conn->query("
    SELECT p.*, pm.name as payment_method 
    FROM payments p 
    LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id 
    WHERE p.ef_id = $id
");
$pay_arr = array();
while ($row = $payments->fetch_array()) {
    $pay_arr[$row['id']] = $row;
}

// Obtener el número de boleta si corresponde a un pago específico
$receipt_no = '';
if (isset($_GET['pid']) && $_GET['pid'] > 0 && isset($pay_arr[$_GET['pid']]['receipt_no'])) {
    $receipt_no = $pay_arr[$_GET['pid']]['receipt_no'];
}
?>
<style>
	.flex {
		display: inline-flex;
		width: 100%;
	}

	.w-50 {
		width: 50%;
	}

	.text-center {
		text-align: center;
	}

	.text-right {
		text-align: right;
	}

	table.wborder {
		width: 100%;
		border-collapse: collapse;
	}

	table.wborder>tbody>tr,
	table.wborder>tbody>tr>td {
		border: 1px solid;
	}

	p {
		margin: unset;
	}
</style>
<div class="container-fluid">
	<div class="text-center mb-3">
		<h4><b><?php echo htmlspecialchars($school_name); ?></b></h4>
		<?php if (!empty($school_address)): ?>
			<p><?php echo htmlspecialchars($school_address); ?></p>
		<?php endif; ?>
		<?php if (!empty($school_contact)): ?>
			<p>Tel: <?php echo htmlspecialchars($school_contact); ?></p>
		<?php endif; ?>
		<hr>
		<h5><b><?php echo $_GET['pid'] == 0 ? "Factura de Pago" : 'Recibo de Pago' ?></b></h5>
		<?php if ($receipt_no): ?>
			<p><b>N° Boleta: <?php echo htmlspecialchars($receipt_no); ?></b></p>
		<?php endif; ?>
	</div>
	<hr>
	<div class="flex">
		<div class="w-50">
			<p>Concepto de pago: <b>
				<?php
					// Mostrar el concepto de pago correctamente
					// Si tienes un campo ef_no, úsalo, si no, muestra el ID o el concepto
					echo isset($ef_no) && $ef_no ? htmlspecialchars($ef_no) : htmlspecialchars($class);
				?>
			</b></p>
			<p>Estudiante: <b><?php echo ucwords($sname) ?></b></p>
			
		</div>
		<?php if ($_GET['pid'] > 0) : ?>
			<div class="w-50">
				<p>Fecha de Pago: <b><?php echo isset($pay_arr[$_GET['pid']]) ? date("M d - Y", strtotime($pay_arr[$_GET['pid']]['date_created'])) : '' ?></b></p>
				<p>Monto de Pago: <b><?php echo isset($pay_arr[$_GET['pid']]) ? number_format($pay_arr[$_GET['pid']]['amount'], 2) : '' ?></b></p>
				 <p>Método de Pago: <b><?php echo isset($pay_arr[$_GET['pid']]['payment_method']) && $pay_arr[$_GET['pid']]['payment_method'] ? $pay_arr[$_GET['pid']]['payment_method'] : 'No especificado' ?></b></p>
				<p>Observación: <b><?php echo isset($pay_arr[$_GET['pid']]) ? $pay_arr[$_GET['pid']]['remarks'] : '' ?></b></p>
			</div>
		<?php endif; ?>
	</div>
	<hr>
	<p><b>Resumen de Pago</b></p>
	<table class="wborder">
		<tr>
			<td width="50%">
				<p><b>Detalles de la tarifa</b></p>
				<hr>
				<table width="100%">
					<tr>
						<td width="50%">Tipo de tarifa</td>
						<td width="50%" class='text-right'>Monto</td>
					</tr>
					<?php
					// Mostrar el monto total del concepto de pago
					$ftotal = $total_fee; // Usar el valor directo de student_ef_list
					?>
					<tr>
						<td><b><?php echo isset($class) ? $class : 'Sin información' ?></b></td>
						<td class='text-right'><b><?php echo number_format($ftotal, 2) ?></b></td>
					</tr>
					<tr>
						<th>Total</th>
						<th class='text-right'><b><?php echo number_format($ftotal, 2) ?></b></th>
					</tr>
				</table>
			</td>
			<td width="50%">
				<p><b>Información de Pago</b></p>
				<table width="100%" class="wborder">
					<tr>
						<td width="50%">Fecha</td>
						<td width="50%" class='text-right'>Monto</td>
					</tr>
					<?php
					$ptotal = 0;
					foreach ($pay_arr as $row) {
						if ($row["id"] <= $_GET['pid'] || $_GET['pid'] == 0) {
							$ptotal += $row['amount'];
					?>
							<tr>
								<td><b><?php echo date("Y-m-d", strtotime($row['date_created'])) ?></b></td>
								<td class='text-right'><b><?php echo number_format($row['amount'], 2) ?></b></td>
							</tr>
					<?php
						}
					}
					?>
					<tr>
						<th>Total</th>
						<th class='text-right'><b><?php echo number_format($ptotal, 2) ?></b></th>
					</tr>
				</table>
				<table width="100%">
					<tr>
						<td>Tarifa total a pagar</td>
						<td class='text-right'><b><?php echo number_format($ftotal, 2) ?></b></td>
					</tr>
					<tr>
						<td>Total Pagado</td>
						<td class='text-right'><b><?php echo number_format($ptotal, 2) ?></b></td>
					</tr>
					<tr>
						<td>Saldo Pendiente</td>
						<td class='text-right'><b><?php echo number_format(max(0, $ftotal - $ptotal), 2) ?></b></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>