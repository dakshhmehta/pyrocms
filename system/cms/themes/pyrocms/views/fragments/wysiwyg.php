<script type="text/javascript" src="<?php echo BASE_URL?>system/cms/themes/pyrocms/build/js/plugins/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo BASE_URL?>system/cms/themes/pyrocms/build/js/plugins/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">

	var instance;

	function update_instance()
	{
		instance = CKEDITOR.currentInstance;
	}

	(function($) {
		$(function(){

			Pyro.init_ckeditor = function(){
				<?php echo $this->parser->parse_string(Settings::get('ckeditor_config'), $this, TRUE); ?>
				//Pyro.init_ckeditor_maximize();
			};
			Pyro.init_ckeditor();

		});
	})(jQuery);
</script>
