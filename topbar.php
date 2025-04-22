<style>
  .logo {
    margin: auto;
    font-size: 20px;
    background: white;
    padding: 7px 11px;
    border-radius: 50% 50%;
    color: #000000b3;
  }
  .profile-img {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
  }
</style>
<div id="page"></div>
<div id="loading"></div>
<nav class="navbar navbar-light fixed-top">
  <div class="container-fluid mt-2 mb-2">
    <div class="col-lg-12">
      <span class="navbar-brand mx-auto font-weight-bold">EduSync</span>
      <div class="float-right">
        <div class="dropdown mr-4">
          <a href="#" class="dropdown-toggle" id="profileMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php 
              $avatarPath = 'assets/uploads/' . ($_SESSION['login_avatar'] ?? 'default-avatar.png');
              $avatar = file_exists($avatarPath) ? $avatarPath : 'assets/uploads/default-avatar.png';
              $userName = $_SESSION['login_name'] ?? 'Usuario';
            ?>
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile" class="profile-img">
            <?php echo htmlspecialchars($userName); ?>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileMenu">
            <a href="javascript:void(0)" id="manage_my_account" class="dropdown-item"><i class="fa fa-user-cog"></i> Editar Perfil</a>
            <a href="javascript:void(0)" id="logout" class="dropdown-item"><i class="fa fa-power-off"></i> Cerrar Sesión</a>
          </div>
        </div>
      </div>
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
      if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
        location.href = 'ajax.php?action=logout';
      }
    });
  });
</script>