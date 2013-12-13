<?php if ($fields): ?>

<?php echo form_open_multipart('', 'class="streams_form"'); ?>

<div class="form_inputs">

	<ul>

	<?php foreach ($fields as $field) { ?>

		<li class="<?php  echo in_array(str_replace($stream->stream_slug.'-'.$stream->stream_namespace.'-', '', $field['input_slug']), $hidden) ? 'hidden' : null;  ?>">
			<?php echo $field['input_row']; ?>
		</li>

	<?php } ?>

	</ul>

</div>

	<?php if ($mode == 'edit') { ?><input type="hidden" value="<?php echo $entry->id;?>" name="row_edit_id" /><?php } ?>

	

<div class="float-right buttons">
	<button type="submit" name="btnAction" value="save" class="btn green"><?php echo lang('buttons:save'); ?></button>
	
	<?php if (! empty($exit_redirect)): ?>
	<button type="submit" name="btnAction" value="save_exit" class="btn green"><?php echo lang('buttons:save_exit'); ?></button>
	<?php endif; ?>

	<?php if (! empty($create_redirect)): ?>
	<button type="submit" name="btnAction" value="save_create" class="btn green"><?php echo lang('buttons:save_create'); ?></button>
	<?php endif; ?>

	<?php if (! empty($continue_redirect)): ?>
	<button type="submit" name="btnAction" value="save_continue" class="btn green"><?php echo lang('buttons:save_continue'); ?></button>
	<?php endif; ?>

	<a href="<?php echo site_url(isset($cancel_uri) ? $cancel_uri : 'admin/streams/entries/index/'.$stream->id); ?>" class="btn gray"><?php echo lang('buttons:cancel'); ?></a>		
</div>


<?php if (isset($disable_form_open) and ! $disable_form_open): echo form_close(); endif; ?>

<?php else: ?>

<div class="no_data">
	<?php

		if (isset($no_fields_message) and $no_fields_message) {
			echo lang_label($no_fields_message);
		} else {
			echo lang('streams:no_fields_msg_first');
		}

	?>
</div><!--.no_data-->

<?php endif;
