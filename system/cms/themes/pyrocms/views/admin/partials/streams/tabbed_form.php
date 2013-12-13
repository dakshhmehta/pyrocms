<?php echo form_open_multipart($form_url, 'class="streams_form"'); ?>

	
	<!-- .nav.nav-tabs -->
	<ul class="nav nav-tabs">
	<?php foreach($tabs as $tab): ?>

		<li class="<?php echo array_search($tab, $tabs) == 0 ? 'active' : null; ?>">
		
			<a href="#<?php echo $tab['id']; ?>" data-toggle="tab" title="<?php echo $tab['title']; ?>">
				<span><?php echo $tab['title']; ?></span>
			</a>

		</li>

	<?php endforeach; ?>
	</ul>
	<!-- /.nav.nav-tabs -->


	<!-- .tab-content.panel-body -->
	<section class="tab-content panel-body">

		<?php foreach($tabs as $tab): ?>

			<div class="tab-pane <?php echo array_search($tab, $tabs) == 0 ? 'active' : null; ?>" id="<?php echo $tab['id']; ?>">
				
				<?php if ( ! empty($tab['content']) and is_string($tab['content'])): ?>

					<?php echo $tab['content']; ?>

				<?php else: ?>

					<?php foreach ($tab['fields'] as $field): ?>

						<div class="form-group <?php echo in_array(str_replace($stream->stream_namespace.'-'.$stream->stream_slug.'-', '', $fields[$field]['input_slug']), $hidden) ? 'hidden' : null; ?>">
							
							<label for="<?php echo $fields[$field]['input_slug'];?>">
								<?php echo lang_label($fields[$field]['input_title']);?> <?php echo $fields[$field]['required'];?>
							</label>

							<?php echo $fields[$field]['input']; ?>

							<?php if( $fields[$field]['instructions'] != '' ): ?>
								<p class="help-block"><?php echo lang_label($fields[$field]['instructions']); ?></p>
							<?php endif; ?>

						</div>

					<?php endforeach; ?>
					
				<?php endif; ?>

			</div>

		<?php endforeach; ?>

	</section>
	<!-- /.tab-content.panel-body -->


	<?php if ($mode == 'edit'): ?>
		<input type="hidden" value="<?php echo $entry->id;?>" name="row_edit_id" />
	<?php endif; ?>

	<div class="panel-footer">
		<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'save_exit', 'cancel'))) ?>
	</div>


<?php echo form_close(); ?>