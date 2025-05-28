<style>
	nav#sidebar {
		overflow-y: auto;
		max-height: calc(100vh - 135px); /* Reducir la altura máxima para dejar espacio al footer */
		transition: all 0.3s ease;
		width: 230px !important;
		min-width: 230px !important;
		/* Ocultar la barra de desplazamiento pero mantener la funcionalidad */
		scrollbar-width: none; /* Firefox */
		-ms-overflow-style: none; /* Internet Explorer y Edge */
		position: fixed;
		top: 65px;
		left: 0;
		height: calc(100% - 135px); /* Reducir altura para que no cubra el footer */
		z-index: 1030; /* Asegurarse que esté por debajo del footer */
		box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
		background: linear-gradient(180deg, #ffffff, #f9f9fc);
		border-right: 1px solid #e8e9f1;
}
	/* Para navegadores basados en WebKit (Chrome, Safari) */
	nav#sidebar::-webkit-scrollbar {
		width: 0;
		display: none;
	}
	#sidebar.collapsed {
		width: 60px !important;
		min-width: 60px !important;
		overflow-x: hidden;
		box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
	}
	#sidebar.collapsed .sidebar-list a span:not(.icon-field) {
		display: none;
	}
	#sidebar.collapsed .sidebar-list .collapse {
		display: none !important;
	}
	#sidebar.collapsed .nav-section {
		text-align: center;
		padding: 10px 0 2px 0;
		opacity: 0.7;
	}
	#sidebar.collapsed .nav-section span {
		display: none;
	}
	#sidebar.collapsed .icon-field {
		margin-right: 0 !important;
		display: block;
		text-align: center;
		width: 52px;
		font-size: 1.5em;
	}
	#sidebar .nav-section {
		font-weight: 600;
		color: #4285f4;
		padding: 12px 10px 2px 10px;
		font-size: 14px;
		letter-spacing: 0.7px;
		text-transform: uppercase;
		transition: all 0.3s;
	}
	#sidebar .nav-section:not(:first-child) {
		margin-top: 12px;
	}
	#sidebar .nav-section hr {
		margin: 4px 0 8px 0;
		border-color: #e8e9f1;
		opacity: 0.6;
	}
	#sidebar .collapse a {
		padding-left: 0 !important;
	#sidebar .collapse .collapse a {
		padding-left: 0 !important;
	}
	#sidebar .nav_collapse {
		margin-bottom: 0 !important;
	}
	#sidebar .nav_collapse + .collapse {
		margin-top: 0 !important;
	}
	#sidebar .nav-item {
		white-space: nowrap;
	}
	
	/* Mejoras para el botón de toggle */	#sidebar-toggle {
		position: fixed;
		top: 75px;
		left: 235px;
		z-index: 1051;
		border: none;
		background: linear-gradient(135deg, #4285f4, #2a75f3);
		color: white;
		width: 36px;
		height: 36px;
		border-radius: 50%;
		box-shadow: 0 3px 8px rgba(66, 133, 244, 0.25);
		display: flex;
		align-items: center;
		justify-content: center;
		transition: all 0.3s;
		cursor: pointer;
	}
	#sidebar-toggle:hover {
		background: linear-gradient(135deg, #2a75f3, #1a65e3);
		box-shadow: 0 5px 12px rgba(66, 133, 244, 0.4);
		transform: translateX(3px);
}
	#sidebar-toggle i {
		transition: transform 0.3s;
	}
		#sidebar.collapsed + #sidebar-toggle {
		left: 57px;
	}
#sidebar.collapsed + #sidebar-toggle i {
		transform: rotate(180deg);
	}
	/* Tooltip para mostrar texto al pasar sobre íconos */
	#sidebar.collapsed .nav-item {
		position: relative;
	}
	#sidebar.collapsed .nav-item:hover::after {
		content: attr(data-title);
		position: absolute;
		left: 60px;
		top: 50%;
		transform: translateY(-50%);
		background: rgba(0,0,0,0.8);
		color: white;
		padding: 4px 8px;
		border-radius: 4px;
		font-size: 12px;
		white-space: nowrap;
		z-index: 1000;
	}
	
	/* Estilos mejorados para los items del navbar */
	#sidebar .nav-item {
		padding: 8px 15px;
		border-radius: 0;
		transition: all 0.3s;
		color: #5a5c69;
		position: relative;
		margin-bottom: 2px;
		border-left: 3px solid transparent;
	}
	
	#sidebar .nav-item:hover {
		background-color: #f8f9fc;
		color: #4285f4;
		border-left-color: #4285f4;
	}
	
	#sidebar .nav-item.active {
		background: linear-gradient(90deg, rgba(66, 133, 244, 0.1), transparent);
		color: #4285f4;
		border-left-color: #4285f4;
		font-weight: 500;
	}
	
	#sidebar .nav-item .icon-field {
		display: inline-block;
		width: 30px;
		text-align: center;
		margin-right: 10px;
		font-size: 1.1rem;
		transition: all 0.3s;
	}
	
	#sidebar .nav-item:hover .icon-field {
		transform: translateX(3px);
		color: #4285f4;
	}
	
	#sidebar .collapse {
		padding-left: 40px;
	}
	
	/* Estilos para los diferentes tipos de usuarios */
	.admin-section .nav-section {
		color: #4285f4 !important;
	}
	.teacher-section .nav-section {
		color: #4285f4 !important;
	}
	.aux-section .nav-section {
		color: #4285f4 !important;
	}
	.student-section .nav-section {
		color: #4285f4 !important;
	}
</style>
<button id="sidebar-toggle" title="Expandir/Colapsar menú"><i class="fa fa-chevron-left"></i></button>
<nav id="sidebar" class='mx-lt-5'>
    <div class="sidebar-list <?php echo $_SESSION['login_type'] == 1 ? 'admin-section' : ($_SESSION['login_type'] == 2 ? 'teacher-section' : ($_SESSION['login_type'] == 3 ? 'aux-section' : 'student-section')); ?>">
        <?php if ($_SESSION['login_type'] == 1): // ADMIN ?>
            <div class="nav-section">Inicio<hr></div>
            <a href="index.php?page=home" class="nav-item nav-home" data-title="Inicio">
                <span class='icon-field'><i class="fa fa-home"></i></span> <span>Inicio</span>
            </a>

            <div class="nav-section">Estudiantes<hr></div>
            <a href="index.php?page=students" class="nav-item nav-students" data-title="Lista de Estudiantes">
                <span class='icon-field'><i class="fa fa-users"></i></span> <span>Lista de Estudiantes</span>
            </a>
            <a href="index.php?page=fees" class="nav-item nav-fees" data-title="Asignar Deudas">
                <span class='icon-field'><i class="fa fa-tasks"></i></span> <span>Asignar Deudas</span>
            </a>
            <a href="index.php?page=asistencia" class="nav-item nav-asistencia" data-title="Asistencia">
                <span class='icon-field'><i class="fa fa-calendar-check"></i></span> <span>Asistencia</span>
            </a>

            <div class="nav-section">Docentes<hr></div>
            <a href="index.php?page=teachers" class="nav-item nav-teachers" data-title="Lista de Docentes">
                <span class='icon-field'><i class="fa fa-chalkboard-teacher"></i></span> <span>Lista de Docentes</span>
            </a>
            <a href="index.php?page=teacher_courses" class="nav-item nav-teacher_courses" data-title="Asignar a Cursos">
                <span class='icon-field'><i class="fa fa-user-tag"></i></span> <span>Asignar a Cursos</span>
            </a>

            <div class="nav-section">Cursos y Conceptos<hr></div>
            <a href="index.php?page=academic_courses" class="nav-item nav-academic_courses" data-title="Cursos Académicos">
                <span class='icon-field'><i class="fa fa-book"></i></span> <span>Cursos Académicos</span>
            </a>
            <a href="index.php?page=concepts" class="nav-item nav-concepts" data-title="Conceptos de Pagos">
                <span class='icon-field'><i class="fa fa-scroll"></i></span> <span>Conceptos de Pagos</span>
            </a>
            <a href="index.php?page=competencias" class="nav-item nav-competencias" data-title="Competencias Globales">
                <span class='icon-field'><i class="fa fa-bullseye"></i></span> <span>Competencias Globales</span>
            </a>

            <div class="nav-section">Pagos<hr></div>
            <a href="index.php?page=payments" class="nav-item nav-payments" data-title="Pagos">
                <span class='icon-field'><i class="fa fa-receipt"></i></span> <span>Pagos</span>
            </a>

            <div class="nav-section">Notas<hr></div>
            <a href="index.php?page=grades" class="nav-item nav-grades" data-title="Notas">
                <span class='icon-field'><i class="fa fa-clipboard-list"></i></span> <span>Notas</span>
            </a>

            <div class="nav-section">Reportes<hr></div>
            <a href="index.php?page=payments_report" class="nav-item nav-payments_report" data-title="Reporte de Pagos">
                <span class='icon-field'><i class="fa fa-th-list"></i></span> <span>Reporte de Pagos</span>
            </a>
            <a href="index.php?page=grades_report" class="nav-item nav-grades_report" data-title="Reporte de Notas">
                <span class='icon-field'><i class="fa fa-chart-bar"></i></span> <span>Reporte de Notas</span>
            </a>
            <a href="index.php?page=attendance_report" class="nav-item nav-attendance_report" data-title="Reporte de Asistencia">
                <span class='icon-field'><i class="fa fa-calendar-alt"></i></span> <span>Reporte de Asistencia</span>
            </a>
            <a href="index.php?page=debt_reports" class="nav-item nav-debt_reports">
                <span class='icon-field'><i class="fa fa-file-invoice-dollar"></i></span> <span>Reporte de Deudas</span>
            </a>

            <div class="nav-section">Usuarios<hr></div>
            <a href="index.php?page=users" class="nav-item nav-users" data-title="Usuarios">
                <span class='icon-field'><i class="fa fa-users"></i></span> <span>Usuarios</span>
            </a>
            <li class="nav-item">
                <a class="nav-link nav-notifications" href="index.php?page=notifications_sender">
                    <i class="fa fa-bell"></i>
                    <span>Notificaciones</span>
                </a>
            </li>
        <?php elseif ($_SESSION['login_type'] == 2): // PROFESOR ?>
            <div class="nav-section">Inicio<hr></div>
            <a href="index.php?page=home" class="nav-item nav-home" data-title="Inicio">
                <span class='icon-field'><i class="fa fa-home"></i></span> <span>Inicio</span>
            </a>
            <div class="nav-section">Académico<hr></div>
            <a href="index.php?page=my_courses" class="nav-item nav-my_courses" data-title="Mis Cursos">
                <span class='icon-field'><i class="fa fa-book"></i></span> <span>Mis Cursos</span>
            </a>
            <a href="index.php?page=grades" class="nav-item nav-grades" data-title="Notas">
                <span class='icon-field'><i class="fa fa-clipboard-list"></i></span> <span>Notas</span>
            </a>
            <a href="index.php?page=competencias" class="nav-item nav-competencias" data-title="Competencias Globales">
                <span class='icon-field'><i class="fa fa-bullseye"></i></span> <span>Competencias Globales</span>
            </a>
            <a href="index.php?page=grades_report" class="nav-item nav-grades_report" data-title="Reporte de Notas">
                <span class='icon-field'><i class="fa fa-chart-bar"></i></span> <span>Reporte de Notas</span>
            </a>
        <?php elseif ($_SESSION['login_type'] == 3): // AUXILIAR ?>
            <div class="nav-section">Inicio<hr></div>
            <a href="index.php?page=home" class="nav-item nav-home" data-title="Inicio">
                <span class='icon-field'><i class="fa fa-home"></i></span> <span>Inicio</span>
            </a>
            <div class="nav-section">Asistencia<hr></div>
            <a href="index.php?page=asistencia" class="nav-item nav-asistencia" data-title="Asistencia">
                <span class='icon-field'><i class="fa fa-calendar-check"></i></span> <span>Asistencia</span>
            </a>
            <div class="nav-section">Reportes<hr></div>
            <a href="index.php?page=attendance_report" class="nav-item nav-attendance_report" data-title="Reporte de Asistencia">
                <span class='icon-field'><i class="fa fa-calendar-alt"></i></span> <span>Reporte de Asistencia</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<script>
    // Submenús colapsables: expandir/contraer solo el menú clickeado
    $('.nav_collapse').click(function(e) {
        e.preventDefault();
        var $submenu = $($(this).attr('href'));
        if ($submenu.hasClass('show')) {
            $submenu.collapse('hide');
        } else {
            $submenu.collapse('show');
        }
    });
    $('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active');

    // Actualizar el ícono cuando cambia el estado
    $('#sidebar-toggle').click(function() {
        $('#sidebar').toggleClass('collapsed');
        if ($('#sidebar').hasClass('collapsed')) {
            localStorage.setItem('sidebar-collapsed', '1');
            $('body').addClass('sidebar-collapsed');
            $(this).find('i').removeClass('fa-chevron-left').addClass('fa-chevron-right');
            // Efecto visual de transición suave
            $(this).css('transform', 'translateX(-3px)');
            setTimeout(function() {
                $('#sidebar-toggle').css('transform', '');
            }, 300);
        } else {
            localStorage.removeItem('sidebar-collapsed');
            $('body').removeClass('sidebar-collapsed');
            $(this).find('i').removeClass('fa-chevron-right').addClass('fa-chevron-left');
            // Efecto visual de transición suave
            $(this).css('transform', 'translateX(3px)');
            setTimeout(function() {
                $('#sidebar-toggle').css('transform', '');
            }, 300);
        }
    });
    
    // Inicializar el estado desde localStorage al cargar
    $(function() {
        if (localStorage.getItem('sidebar-collapsed') === '1') {
            $('#sidebar').addClass('collapsed');
            $('body').addClass('sidebar-collapsed');
			            $('#sidebar-toggle i').removeClass('fa-chevron-left').addClass('fa-chevron-right');
}
    });
</script>