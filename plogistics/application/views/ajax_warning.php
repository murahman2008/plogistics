<?php if(isset($warningArray) && count($warningArray) > 0) : ?>
	<div class = "row zero_padding" style = "width:700px; height:200px;">
		<div class = "col-xs-12">
			<?php 
				$warning = implode("<br/><hr/><br/>", $warningArray);
				echo $warning;
			?>
		</div>
	</div>		
	<div class = "row zero_padding zero_margin">
		<div class = "col-xs-6 left">
			<input type = "button" name = "ajax_warning_confirm" id = "ajax_warning_confirm" value = "Confirm" onclick = "onAjaxWarningConfirm(this);" />
		</div>
		<div class = "col-xs-6 right">	
			<input type = "button" name = "ajax_warning_cancel" id = "ajax_warning_cancel" value = "Cancel" onclick = "onAjaxWarningCancel(this);" />
		</div>
	</div>		
	
	<script type = "text/javascript">
		function onAjaxWarningConfirm(btn) {
			<?php 
				if(isset($onConfirmFunction) && trim($onConfirmFunction) !== '')
					echo $onConfirmFunction;
			?>
			return false;
		}

		function onAjaxWarningCancel(btn) {
			$.fancybox.close();
			return false;
		}

		$(function() {
			<?php 
				if(isset($onLoadFunction) && trim($onLoadFunction) !== '') 
					echo $onLoadFunction;
			?>
		});
		
	</script>

<?php endif; ?>
