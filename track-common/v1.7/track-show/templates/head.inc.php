<?php if (!$include_flag){exit();} ?>
<title>CPA Tracker<?php if($_GET['page'] == 'support') echo ' ' . _TRACK_VER; ?></title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- <?php echo _HTML_ROOT_PATH . '/' . _HTML_LIB_PATH;; ?> -->
<!-- <?php echo _HTML_TRACK_PATH; ?> -->
<!-- <?php echo realpath(_HTML_TRACK_PATH); ?> -->

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<!-- Bootstrap -->
<link rel="stylesheet" type="text/css" href="<?php echo _HTML_LIB_PATH;?>/bootstrap/css/bootstrap.min.css">

<!-- PLUGINS CSS -->
<link rel="stylesheet" href="<?php echo _HTML_LIB_PATH;?>/bootstrap/plugins/icheck/skin/skin.css" >

<!-- Main Styles -->
<link rel="stylesheet" type="text/css" href="<?php echo _HTML_LIB_PATH;?>/../templates/css/main.css">	

<!-- Custom Styles -->	
<link rel="stylesheet" href="<?php echo _HTML_LIB_PATH;?>/bootstrap/fonts/font-awesome-4.2.0/css/font-awesome.min.css">	
<link rel="stylesheet" href="<?php echo _HTML_LIB_PATH;?>/bootstrap/fonts/pfagorasanspro/pfagorasanspro.css">	
<link rel="stylesheet" href="<?php echo _HTML_LIB_PATH;?>/bootstrap/plugins/bootstrap-select/dist/css/bootstrap-select.min.css">	

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->