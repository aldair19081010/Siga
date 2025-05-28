<?php include 'db_connect.php' ?>
<style>
/* Estilos modernizados para el dashboard de inicio */
.inicio-dashboard {
    max-width: 1000px;
    margin: 30px auto 0 auto;
    background: linear-gradient(to bottom, #ffffff, #f9f9fc);
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(66, 133, 244, 0.08);
    padding: 40px 30px 35px 30px;
    text-align: center;
    border-top: 4px solid #4285f4;
}

.inicio-dashboard img {
    width: 160px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    padding: 5px;
    background: white;
}

.inicio-dashboard h2 {
    font-weight: 700;
    color: #4285f4;
    margin-bottom: 15px;
    font-size: 2.2rem;
    text-shadow: 0 1px 1px rgba(0,0,0,0.05);
}

.inicio-dashboard p {
    font-size: 1.18rem;
    color: #5a5c69;
    margin-bottom: 30px;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.inicio-cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    margin-top: 30px;
}

.inicio-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 25px 20px;
    width: 210px;
    min-height: 130px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    text-decoration: none;
    border: 1px solid #e3e6f0;
    position: relative;
    overflow: hidden;
}

.inicio-card:before {
    content: '';
    position: absolute;
    width: 100%;
    height: 3px;
    top: 0;
    left: 0;
    background: linear-gradient(90deg, #4285f4, #2a75f3);
    opacity: 0;
    transition: opacity 0.3s;
}

.inicio-card:hover:before {
    opacity: 1;
}

.inicio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(66, 133, 244, 0.15);
    border-color: #cdd8f6;
}

.inicio-card:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 12px;
    box-shadow: 0 0 0 0 rgba(66, 133, 244, 0);
    transition: box-shadow 0.3s;
    z-index: -1;
}

.inicio-card:hover:after {
    box-shadow: 0 0 20px 5px rgba(66, 133, 244, 0.2);
}

.inicio-card i {
    font-size: 2.4rem;
    color: #4285f4;
    margin-bottom: 12px;
    transition: all 0.3s;
}

.admin-card i { color: #4285f4; }
.teacher-card i { color: #4285f4; }
.aux-card i { color: #f6c23e; }
.student-card i { color: #36b9cc; }

.inicio-card:hover i {
    transform: scale(1.2);
}

.inicio-card span {
    font-size: 1.1rem;
    font-weight: 600;
    color: #5a5c69;
    transition: color 0.3s;
}
/* Estilos del banner de bienvenida */
.welcome-banner {
    background: linear-gradient(135deg, #f8f9fc, #eaecf4);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
    border-left: 4px solid #4285f4;
    text-align: left;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.welcome-icon {
    background: linear-gradient(135deg, #4285f4, #2a75f3);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 20px;
    box-shadow: 0 3px 6px rgba(66, 133, 244, 0.3);
}

.welcome-banner h3 {
    margin: 0 0 5px 0;
    font-weight: 600;
    color: #4285f4;
    font-size: 1.3rem;
}

.welcome-banner p {
    margin: 0;
    font-size: 1rem;
    color: #6e707e;
}

/* Estilos para diferentes tipos de tarjetas */
.admin-card:hover {
    background: linear-gradient(135deg, #4285f4, #2a75f3);
}
.admin-card:hover i, .admin-card:hover span {
    color: #fff;
}

.teacher-card:hover {
    background: linear-gradient(135deg, #4285f4, #2a75f3);
}
.teacher-card:hover i, .teacher-card:hover span {
    color: #fff;
}

.aux-card:hover {
    background: linear-gradient(135deg, #f6c23e, #dda20a);
}
.aux-card:hover i, .aux-card:hover span {
    color: #fff;
}

.student-card:hover {
    background: linear-gradient(135deg, #36b9cc, #258391);
}
.student-card:hover i, .student-card:hover span {
    color: #fff;
}

/* Pie de página */
.inicio-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e3e6f0;
    color: #858796;
    font-size: 0.9rem;
    text-align: center;
}

.inicio-footer p {
    margin-bottom: 10px;
    font-size: 0.9rem;
}

/* Para dispositivos móviles */
@media (max-width: 992px) {
    .inicio-dashboard {
        margin: 20px 15px;
        padding: 25px 15px;
    }
    
    .inicio-cards {
        gap: 15px;
    }
    
    .inicio-card {
        width: calc(50% - 15px);
        min-height: 120px;
        padding: 15px;
    }
    
    .welcome-banner {
        flex-direction: column;
        text-align: center;
        padding: 15px;
    }
    
    .welcome-icon {
        margin: 0 0 15px 0;
    }
}

@media (max-width: 576px) {
    .inicio-cards {
        flex-direction: column;
        align-items: center;
    }
    
    .inicio-card {
        width: 100%;
    }
    
    .inicio-dashboard h2 {
        font-size: 1.8rem;
    }
    
    .inicio-dashboard p {
        font-size: 1rem;
    }
}
</style>

<!-- Incluir Animate.css para algunas animaciones sutiles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<div class="inicio-dashboard">
	<img src="assets/uploads/logo.jpg" alt="Logo EduSync">
	<h2>Inicio</h2>
	<p>
		<?php
		$user = $_SESSION['login_name'] ?? '';
		echo $user ? "Hola, <b>" . htmlspecialchars($user) . "</b>.<br>" : "";
		?>
		Selecciona una opción para continuar:
	</p>
	<div class="welcome-banner">
		<div class="welcome-icon"><i class="fa fa-home"></i></div>
		<h3>Bienvenido(a) al Sistema de Gestión Educativa</h3>
		<p>Accede rápidamente a las funciones que necesitas desde esta página de inicio.</p>
	</div>
	
	<div class="inicio-cards">
		<?php if ($_SESSION['login_type'] == 1): // ADMIN ?>
			<a href="index.php?page=students" class="inicio-card admin-card" data-toggle="tooltip" title="Gestionar estudiantes">
				<i class="fa fa-users"></i>
				<span>Estudiantes</span>
			</a>
			<a href="index.php?page=fees" class="inicio-card admin-card" data-toggle="tooltip" title="Ver pagos de estudiantes">
				<i class="fa fa-money-check"></i>
				<span>Pagos de Estudiantes</span>
			</a>
			<a href="index.php?page=grades_report" class="inicio-card admin-card" data-toggle="tooltip" title="Ver reportes de calificaciones">
				<i class="fa fa-chart-bar"></i>
				<span>Reporte de Notas</span>
			</a>
			<a href="index.php?page=asistencia" class="inicio-card admin-card" data-toggle="tooltip" title="Gestionar asistencia">
				<i class="fa fa-calendar-check"></i>
				<span>Asistencia</span>
			</a>
			<a href="index.php?page=teachers" class="inicio-card admin-card" data-toggle="tooltip" title="Gestionar docentes">
				<i class="fa fa-chalkboard-teacher"></i>
				<span>Docentes</span>
			</a>
			<a href="index.php?page=users" class="inicio-card admin-card" data-toggle="tooltip" title="Administrar usuarios del sistema">
				<i class="fa fa-users-cog"></i>
				<span>Usuarios</span>
			</a>
			<a href="index.php?page=payments" class="inicio-card admin-card" data-toggle="tooltip" title="Ver registro de pagos">
				<i class="fa fa-receipt"></i>
				<span>Pagos</span>
			</a>
			<a href="index.php?page=concepts" class="inicio-card admin-card" data-toggle="tooltip" title="Gestionar conceptos de pago">
				<i class="fa fa-scroll"></i>
				<span>Conceptos de Pagos</span>
			</a>
			<a href="index.php?page=academic_courses" class="inicio-card admin-card" data-toggle="tooltip" title="Gestionar cursos académicos">
				<i class="fa fa-book"></i>
				<span>Cursos Académicos</span>
			</a>
			<a href="index.php?page=teacher_courses" class="inicio-card admin-card" data-toggle="tooltip" title="Asignar docentes a cursos">
				<i class="fa fa-user-tag"></i>
				<span>Asignar Docentes</span>
			</a>
			<a href="index.php?page=attendance_report" class="inicio-card admin-card" data-toggle="tooltip" title="Ver reportes de asistencia">
				<i class="fa fa-calendar-alt"></i>
				<span>Reporte de Asistencia</span>
			</a>
			<a href="index.php?page=debt_reports" class="inicio-card admin-card" data-toggle="tooltip" title="Ver reportes de deudas">
				<i class="fa fa-file-invoice-dollar"></i>
				<span>Reporte de Deudas</span>
			</a>
		<?php elseif ($_SESSION['login_type'] == 2): // PROFESOR ?>
			<a href="index.php?page=my_courses" class="inicio-card teacher-card" data-toggle="tooltip" title="Ver mis cursos asignados">
				<i class="fa fa-book"></i>
				<span>Mis Cursos</span>
			</a>
			<a href="index.php?page=grades" class="inicio-card teacher-card" data-toggle="tooltip" title="Gestionar notas de estudiantes">
				<i class="fa fa-clipboard-list"></i>
				<span>Notas</span>
			</a>
			<a href="index.php?page=competencias" class="inicio-card teacher-card" data-toggle="tooltip" title="Gestionar competencias">
				<i class="fa fa-star-half-alt"></i>
				<span>Competencias</span>
			</a>
			<a href="index.php?page=grades_report" class="inicio-card teacher-card" data-toggle="tooltip" title="Ver reportes de calificaciones">
				<i class="fa fa-chart-line"></i>
				<span>Reporte de Notas</span>
			</a>
		<?php elseif ($_SESSION['login_type'] == 3): // AUXILIAR ?>
			<a href="index.php?page=asistencia" class="inicio-card aux-card" data-toggle="tooltip" title="Registrar asistencia">
				<i class="fa fa-calendar-check"></i>
				<span>Asistencia</span>
			</a>
			<a href="index.php?page=attendance_report" class="inicio-card aux-card" data-toggle="tooltip" title="Ver reportes de asistencia">
				<i class="fa fa-calendar-alt"></i>
				<span>Reporte de Asistencia</span>
			</a>
			<a href="index.php?page=students" class="inicio-card aux-card" data-toggle="tooltip" title="Ver lista de estudiantes">
				<i class="fa fa-users"></i>
				<span>Estudiantes</span>
			</a>
		<?php elseif ($_SESSION['login_type'] == 4): // ESTUDIANTE ?>
			<a href="index.php?page=my_payments" class="inicio-card student-card" data-toggle="tooltip" title="Ver mis pagos realizados">
				<i class="fa fa-receipt"></i>
				<span>Mis Pagos</span>
			</a>
			<a href="index.php?page=my_debts" class="inicio-card student-card" data-toggle="tooltip" title="Ver mis deudas pendientes">
				<i class="fa fa-money-bill-wave"></i>
				<span>Mis Deudas</span>
			</a>
			<a href="index.php?page=my_grades" class="inicio-card student-card" data-toggle="tooltip" title="Ver mis calificaciones">
				<i class="fa fa-clipboard-list"></i>
				<span>Mis Notas</span>
			</a>
		<?php endif; ?>
	</div>
	
	<div class="inicio-footer">
		<p>&copy; <?php echo date('Y'); ?> Sistema de Gestión Educativa</p>
		<p>Versión 2.0</p>
	</div>
</div>

<script>
$(document).ready(function(){
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Añadir animaciones a las cards
    $('.inicio-card').addClass('animate__animated animate__fadeIn');
    
    // Efecto de pulsación al hacer clic
    $('.inicio-card').on('mousedown', function() {
        $(this).css('transform', 'scale(0.98)');
    });
    
    $('.inicio-card').on('mouseup mouseleave', function() {
        $(this).css('transform', 'translateY(-5px)');
    });
});
</script>