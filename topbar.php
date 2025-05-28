<?php
include 'db_connect.php';
?>
<style>
  .logo {
    margin: auto;
    font-size: 22px;
    background: white;
    padding: 8px 12px;
    border-radius: 50%;
    color: #4285f4;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
  }
  .logo:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  }
  .profile-img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    margin-right: 8px;
    transition: all 0.3s ease;
  }
  .navbar {
    background: linear-gradient(135deg, #4285f4, #2a75f3);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: 65px;
    padding: 0;
  }
  .navbar-brand-container {
    padding-left: 10px;
  }
  .navbar-brand {
    color: #fff !important;
    font-size: 1.6rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    padding-left: 5px;
  }
  .right-section {
    padding-right: 10px;
  }
  .dropdown-toggle {
    color: #fff;
    font-weight: 500;
    display: flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.2s;
    text-decoration: none;
  }
  .dropdown-toggle:hover, .dropdown-toggle:focus {
    background-color: rgba(255,255,255,0.15);
    color: #fff;
    text-decoration: none;
  }
  .dropdown-menu {
    border-top: 3px solid #4285f4;
    animation: fadeInDropdown 0.3s ease;
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 8px;
    margin-top: 10px;
  }
  @keyframes fadeInDropdown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .dropdown-header {
    background-color: rgba(66, 133, 244, 0.05);
    color: #4285f4;
    font-weight: 600;
  }
  .dropdown-item {
    border-radius: 6px;
    padding: 8px 15px;
    transition: all 0.2s;
    color: #5a5c69;
    position: relative;
    overflow: hidden;
  }
  .dropdown-item i {
    margin-right: 8px;
    color: #4285f4;
  }
  .dropdown-item:hover, .dropdown-item:focus {
    background-color: #f8f9fc;
    color: #4285f4;
  }
  .dropdown-item::after {
    content: '';
    display: block;
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
    background-image: radial-gradient(circle, #4285f4 10%, transparent 10.01%);
    background-repeat: no-repeat;
    background-position: 50%;
    transform: scale(10, 10);
    opacity: 0;
    transition: transform .5s, opacity 1s;
  }
  .dropdown-item:active::after {
    transform: scale(0, 0);
    opacity: .2;
    transition: 0s;
  }
  #loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255,255,255,0.85);
    display: none;
    z-index: 1050;
    align-items: center;
    justify-content: center;
  }
</style>
<!-- Los divs page y loading ahora se manejan dentro de la estructura principal del navbar -->
<nav class="navbar navbar-expand fixed-top">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center w-100">
      <div class="navbar-brand-container ml-3">
        <span class="navbar-brand font-weight-bold">
        <i class="fa fa-graduation-cap mr-2"></i>EduSync
        </span>
      </div>
      
      <div class="right-section d-flex align-items-center mr-3">
        <div class="dropdown">
          <a href="#" class="dropdown-toggle" id="profileMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php 
              // Obtener el nombre del usuario de la sesión
              $userName = $_SESSION['login_name'] ?? '';
              // Si no está seteado, intenta obtenerlo de la base de datos
              if (empty($userName) && isset($_SESSION['login_id'])) {
                  $uid = intval($_SESSION['login_id']);
                  $q = $conn->query("SELECT name FROM users WHERE id = $uid");
                  if ($q && $q->num_rows > 0) {
                      $userName = $q->fetch_assoc()['name'];
                      $_SESSION['login_name'] = $userName;
                  }
              }
              $avatarFile = $_SESSION['login_avatar'] ?? '';
              $avatarPath = !empty($avatarFile) ? 'assets/uploads/' . $avatarFile : '';
              $avatarExists = (!empty($avatarFile) && file_exists($avatarPath));
              $avatarUrl = $avatarExists
                ? $avatarPath
                : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=4285f4&color=fff&size=128';
            ?>
            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Profile" class="profile-img">
            <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
            <i class="fa fa-chevron-down ml-1" style="font-size: 0.8rem;"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileMenu">
            <div class="dropdown-header px-3 py-2">
              <small class="text-muted">Opciones de usuario</small>
            </div>
            <a href="javascript:void(0)" id="manage_my_account" class="dropdown-item">
              <i class="fa fa-user-cog"></i> Editar Perfil
            </a>
            <div class="dropdown-divider"></div>
            <a href="javascript:void(0)" id="logout" class="dropdown-item">
              <i class="fa fa-power-off"></i> Cerrar Sesión
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Indicador de carga -->
  <div id="loading">
    <div class="spinner-border" role="status" style="color: #4285f4;">
      <span class="sr-only">Cargando...</span>
    </div>
  </div>
</nav>

<script>
  $(document).ready(function() {
    // Inicializar el menú desplegable de Bootstrap
    $('.dropdown-toggle').dropdown();

    // Manejar clic en "Editar Perfil"
    $('#manage_my_account').click(function() {
      uni_modal("Gestionar Perfil", "manage_user.php?id=<?php echo $_SESSION['login_id'] ?>&mtype=own", "mid-large");
    });

    // Manejar clic en "Cerrar Sesión"
    $('#logout').click(function() {
      $('#loading').fadeIn('fast');
      if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
        location.href = 'ajax.php?action=logout';
      } else {
        $('#loading').fadeOut('fast');
      }
    });
    
    // Añadir efecto de brillo al pasar el cursor por la imagen de perfil
    $('.profile-img').hover(
      function() { $(this).css('transform', 'scale(1.05)'); },
      function() { $(this).css('transform', 'scale(1)'); }
    );
    
    // Agregar animación para elementos del navbar
    $('.navbar-brand').addClass('animate__animated animate__fadeIn');
    
    // Mostrar/ocultar indicador de carga durante transiciones de página
    $(document).ajaxStart(function() {
      $('#loading').fadeIn('fast');
    }).ajaxStop(function() {
      $('#loading').fadeOut('fast');
    });
    
    // Funcionalidad para notificaciones (demo)
    $('.notification-icon').click(function() {
      alert('Sistema de notificaciones en desarrollo');
    });
  });
</script>

<!-- Agregar estilos adicionales para las notificaciones -->
<style>
  .notification-icon {
    position: relative;
    display: inline-block;
    padding: 5px;
    color: white;
    border-radius: 50%;
    height: 35px;
    width: 35px;
    text-align: center;
    line-height: 25px;
    transition: all 0.2s;
  }
  
  .notification-icon:hover {
    background-color: rgba(255,255,255,0.15);
    color: white;
    text-decoration: none;
  }
  
  .notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background-color: #ff5252;
    color: white;
    border-radius: 50%;
    padding: 0.25rem;
    font-size: 0.65rem;
    min-width: 18px;
    height: 18px;
    line-height: 10px;
    text-align: center;
    font-weight: bold;
    border: 1.5px solid #fff;
  }
  
  .user-name {
    max-width: 130px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    vertical-align: middle;
    margin: 0 6px;
  }
  
  .dropdown-header {
    font-weight: 600;
    background-color: #f8f9fc;
    border-radius: 6px 6px 0 0;
    color: #5a5c69;
  }
  
  .dropdown-divider {
    margin: 0.3rem 0;
    border-top-color: #eaecf4;
  }
  
  @media (max-width: 768px) {
    .user-name {
      max-width: 80px;
    }
    
    .navbar-brand {
      font-size: 1.3rem;
    }
  }
</style>