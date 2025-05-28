<!DOCTYPE html>
<html lang="en">

<?php
session_start();
if (!isset($_SESSION['login_id'])) {
	header('location:login.php'); // Redirigir al login si no hay sesión activa
	exit;
}
?>

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <?php include('./header.php'); ?>

  <style>
    /* Ajustes para evitar solapamiento del contenido con el navbar y footer */
    #view-panel {
      margin-left: 270px;
      padding: 10px 15px 10px 15px;
      transition: all 0.3s ease;
      width: calc(100% - 270px);
      position: relative;
      margin-top: 65px;
      /* Aumentar padding-bottom para dar más espacio al footer */
      padding-bottom: 100px;
    }
    
    /* Estado colapsado - ampliar el espacio de contenido */
    .sidebar-collapsed #view-panel {
      margin-left: 60px;
      width: calc(100% - 60px);
    }
    
    /* Indicador de estado del sidebar para mejor UX */
    .sidebar-toggle-hint {
      position: fixed;
      top: 75px;
      left: 320px;
      opacity: 0;
      background: rgba(0,0,0,0.7);
      color: white;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 12px;
      transition: opacity 0.3s;
      pointer-events: none;
      z-index: 1100;
    }
    #sidebar-toggle:hover + .sidebar-toggle-hint {
      opacity: 1;
    }
    
    /* Ajustes para el footer */
    body {
      min-height: 100vh;
      position: relative;
      margin: 0;
      padding-bottom: 80px; /* Altura del footer */
      background: #f8f9fa;
      overflow-x: hidden;
    }
    
    footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      z-index: 1040; /* Mayor que el navbar pero menor que modales */
      height: auto; /* Altura automática según contenido */
      background-color: #4285f4 !important;
      box-shadow: 0 -3px 15px rgba(0,0,0,0.1);
      padding: 15px 0;
      transition: all 0.3s ease;
    }
    
    footer a {
      position: relative;
      transition: all 0.3s ease;
    }
    
    footer a:hover {
      color: #ffffff !important;
      text-shadow: 0 0 5px rgba(255,255,255,0.5);
    }
    
    footer a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -2px;
      left: 0;
      background-color: #ffffff;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    
    footer a:hover::after {
      visibility: visible;
      width: 100%;
    }
    
    /* Ajustes para correcta visualización en dispositivos móviles */
    @media (max-width: 992px) {
      #view-panel {
        margin-left: 60px;
        width: calc(100% - 60px);
      }
      #sidebar {
        width: 60px !important;
        min-width: 60px !important;
      }
      #sidebar .sidebar-list a span:not(.icon-field) {
        display: none;
      }
      #sidebar .sidebar-list .collapse {
        display: none !important;
      }
      #sidebar-toggle {
        left: 65px;
      }
    }
  </style>
</head>

<body>
  <?php include 'topbar.php' ?>
  <?php include 'navbar.php' ?>
  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
    </div>
  </div>

  <main id="view-panel">
    <?php
      // Mostrar siempre la sección de inicio al ingresar, para todos los roles
      $page = isset($_GET['page']) ? $_GET['page'] : 'home';
      include $page . '.php';
    ?>
  </main>

  <!-- Footer con nuevo color y estilo mejorado -->
  <footer class="footer mt-auto py-3 text-white text-center" style="background-color: #4285f4; box-shadow: 0 -3px 10px rgba(0,0,0,0.1);">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-12">
          Para más información: <a class="text-white font-weight-bold" href="https://www.facebook.com/aldairalberto.cherovelasquez/" style="text-decoration: none; border-bottom: 1px dotted rgba(255,255,255,0.7); padding-bottom: 1px;">@AldairChero</a>
        </div>
      </div>
    </div>
  </footer>

  <div id="preloader"></div>
  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

  <div class="modal fade" id="confirm_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmación</h5>
        </div>
        <div class="modal-body">
          <div id="delete_content"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id='confirm' onclick="">Continuar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Guardar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="viewer_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
        <img src="" alt="">
      </div>
    </div>
  </div>
</body>

<script>
  // Agregar clase para manejo de sidebar colapsado
  function updateSidebarState() {
    if (localStorage.getItem('sidebar-collapsed') === '1') {
      $('body').addClass('sidebar-collapsed');
    } else {
      $('body').removeClass('sidebar-collapsed');
    }
  }
  
  // Llamar cuando se carga la página
  $(document).ready(function() {
    updateSidebarState();
    $('#preloader').fadeOut('fast', function() {
      $(this).remove();
    });
  });
  
  // Modificar el toggle para actualizar clase del body
  $('#sidebar-toggle').on('click', function() {
    $('#sidebar').toggleClass('collapsed');
    if ($('#sidebar').hasClass('collapsed')) {
      localStorage.setItem('sidebar-collapsed', '1');
      $('body').addClass('sidebar-collapsed');
    } else {
      localStorage.removeItem('sidebar-collapsed');
      $('body').removeClass('sidebar-collapsed');
    }
  });
  
  window.start_load = function() {
    $('body').prepend('<di id="preloader2"></di>')
  }
  window.end_load = function() {
    $('#preloader2').fadeOut('fast', function() {
      $(this).remove();
    })
  }
  window.viewer_modal = function($src = '') {
    start_load()
    var t = $src.split('.')
    t = t[1]
    if (t == 'mp4') {
      var view = $("<video src='" + $src + "' controls autoplay></video>")
    } else {
      var view = $("<img src='" + $src + "' />")
    }
    $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
    $('#viewer_modal .modal-content').append(view)
    $('#viewer_modal').modal({
      show: true,
      backdrop: 'static',
      keyboard: false,
      focus: true
    })
    end_load()

  }
  window.uni_modal = function($title = '', $url = '', $size = "") {
    start_load();
    $.ajax({
        url: $url,
        error: err => {
            console.log(err);
            alert("Ocurrió un error");
        },
        success: function(resp) {
            if (resp) {
                $('#uni_modal .modal-title').html($title);
                $('#uni_modal .modal-body').html(resp);
                if ($size != '') {
                    $('#uni_modal .modal-dialog').addClass($size);
                } else {
                    $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md");
                }
                
                // Ajustar posición del modal para evitar superposición con el navbar
                $('#uni_modal').on('show.bs.modal', function () {
                    var navbar = $('#topbar').outerHeight() || 0;
                    var windowHeight = $(window).height();
                    var modalHeight = $('#uni_modal .modal-content').height();
                    var modalMargin = Math.max(20, navbar + 10); // Al menos 20px o 10px más que el navbar
                    
                    // Si el modal es más alto que la ventana menos el margen, ajustar el overflow
                    if (modalHeight > (windowHeight - (modalMargin * 2))) {
                        $('#uni_modal .modal-body').css('max-height', (windowHeight - (modalMargin * 2) - 120) + 'px');
                        $('#uni_modal .modal-body').css('overflow-y', 'auto');
                    }
                    
                    $('#uni_modal .modal-dialog').css('margin-top', modalMargin + 'px');
                });
                
                $('#uni_modal').modal({
                    show: true,
                    backdrop: 'static',
                    keyboard: false,
                    focus: true
                }).removeAttr('aria-hidden');
                
                end_load();
            }
        }
    });
  }
  window._conf = function($msg = '', $func = '', $params = []) {
    $('#confirm_modal #confirm').attr('onclick', $func + "(" + $params.join(',') + ")")
    $('#confirm_modal .modal-body').html($msg)
    $('#confirm_modal').modal('show')
  }
  window.alert_toast = function($msg = 'TEST', $bg = 'success') {
    $('#alert_toast').removeClass('bg-success')
    $('#alert_toast').removeClass('bg-danger')
    $('#alert_toast').removeClass('bg-info')
    $('#alert_toast').removeClass('bg-warning')

    if ($bg == 'success')
      $('#alert_toast').addClass('bg-success')
    if ($bg == 'danger')
      $('#alert_toast').addClass('bg-danger')
    if ($bg == 'info')
      $('#alert_toast').addClass('bg-info')
    if ($bg == 'warning')
      $('#alert_toast').addClass('bg-warning')
    $('#alert_toast .toast-body').html($msg)
    $('#alert_toast').toast({
      delay: 3000
    }).toast('show');
  }
  $(document).ready(function() {
    $('#preloader').fadeOut('fast', function() {
      $(this).remove();
    })
  })
  $('.datetimepicker').datetimepicker({
    format: 'Y/m/d H:i',
    startDate: '+3d'
  })
  $('.select2').select2({
    placeholder: "Porfavor selecciona aquí",
    width: "100%"
  })
</script>

<?php include('./footer.php'); ?>

</html>