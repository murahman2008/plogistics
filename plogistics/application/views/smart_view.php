<html>
	<head>
		<script type = "text/javascript" src = "<?php echo base_url('javascript/jquery/jquery.js'); ?>"></script>
		<script type = "text/javascript" src = "<?php echo base_url('javascript/jquery/jquery-ui.js'); ?>"></script>
		<script type = "text/javascript" src = "<?php echo base_url('javascript/fancybox/jquery.fancybox.js'); ?>"></script>
		<script type = "text/javascript" src = "<?php echo base_url('javascript/bootstrap/bootstrap.min.js'); ?>"></script>
		<script type = "text/javascript" src = "<?php echo base_url('javascript/fancytree/dist/jquery.fancytree-all.min.js'); ?>"></script>
		<script type = "text/javascript" src = "<?php echo base_url('javascript/__basic_check.js'); ?>"></script>
		<script type = "text/javascript" src = "<?php echo base_url('javascript/main.js'); ?>"></script>
		
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('css/bootstrap/bootstrap.css'); ?>">
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('css/bootstrap/bootstrap-theme.min.css'); ?>">
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('css/jquery-ui.css'); ?>">
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('css/jquery-ui.structure.css'); ?>">
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('css/jquery-ui.theme.css'); ?>">
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('css/fancybox/jquery.fancybox.css'); ?>">
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('javascript/fancytree/dist/skin-win8/ui.fancytree.css'); ?>">
		<link rel = "stylesheet" type = "text/css" href = "<?php echo base_url('css/main.css'); ?>">
	</head>
	<bdoy>
		<?php if(isset($source) && ($source = trim($source)) !== '' && file_exists(FCPATH.'application/views/'.$source.'.php')) : ?> 
			
			<!-- PRINT HEADER -->
			<?php if(isset($header) && $header === true ) : ?>
				<?php 
					if(!isset($currentMenu) || ($currentMenu = trim($currentMenu)) === '')
						$currentMenu = 'sell_order';
				
					$this->load->view('header', array('currentMenu' => $currentMenu));
				?>
			<?php endif; ?>
			<!-- ---------------- -->	
			
			<?php 
				if(($errorMsg = getSessionData('view_error_message')) !== false)
				{
					$errorMsg = (is_array($errorMsg) ? implode("<br/>", $errorMsg) : trim($errorMsg));
					//echo '<div id = "exception_container">'.$errorMsg.'</div>';
					echo '<div class="alert alert-danger alert-dismissible" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
							.$errorMsg.
						  '</div>';
					clearSessionData('view_error_message');
				}
				else if(($successMsg = getSessionData('view_success_message')) !== false)
				{
					$successMsg = (is_array($successMsg) ? implode("<br/>", $successMsg) : trim($successMsg));
					//echo '<div id = "success_container">'.$successMsg.'</div>';
					echo '<div class="alert alert-success alert-dismissible" role="alert">'.$successMsg.'</div>';
					clearSessionData('view_success_message');
				}	
			?>
			
			<div class = "container">
				<?php 
					if(!isset($data) || !is_array($data))
						$data = array();
				
					$this->load->view($source, $data);					
				?>
			</div>
			
			<!-- PRINT FOOTER -->
			<?php if(isset($footer) && $footer === true ) : ?>
				<?php 
					$this->load->view('footer');
				?>
			<?php endif; ?>
			<!-- ---------------- -->	
		<?php else : ?>
			<h3>Cannot identity/find what page you are looking for. Go To <a href = "<?php echo site_url(''); ?>">Home</a></h3>			
		<?php endif; ?>	
	</bdoy>
</html>



