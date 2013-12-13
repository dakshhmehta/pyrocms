<div class="form-group">
<div class="row">
	
	<label class="col-lg-2" for="title"><?php if (isset($page->type->title_label)): echo lang_label($page->type->title_label); else: echo lang('global:title'); endif ?> <span>*</span></label>

	<div class="col-lg-10">
		<?php echo form_input('title', $page->title, 'id="title" class="form-control" maxlength="60"') ?>
	</div>

</div>
</div>


<div class="form-group">
<div class="row">
	
	<label class="col-lg-2" for="slug"><?php echo lang('global:slug') ?>  <span>*</span></label>

	<div class="col-lg-10">
		<?php if (isset($parent_page)): ?>
			<?php echo site_url($parent_page->uri) ?>/
		<?php else: ?>
			<?php echo site_url() . (config_item('index_page') ? '/' : '') ?>
		<?php endif ?>

		<?php if ($this->method == 'edit'): ?>
			<?php echo form_hidden('old_slug', $page->slug) ?>
		<?php endif ?>

		<?php if (in_array($page->slug, array('home', '404'))): ?>
			<?php echo form_hidden('slug', $page->slug) ?>
			<?php echo form_input('', $page->slug, 'id="slug" class="" size="20" disabled="disabled"') ?>
		<?php else: ?>
			<?php echo form_input('slug', $page->slug, 'id="slug" size="20" class=" '.($this->method == 'edit' ? ' disabled' : '').'"') ?>
		<?php endif ?>

		<?php echo config_item('url_suffix') ?>
	</div>

</div>
</div>


<div class="form-group">
<div class="row">
	
	<label class="col-lg-2" for="category_id"><?php echo lang('pages:status_label') ?></label>

	<div class="col-lg-10">
		<?php echo form_dropdown('status', array('draft'=>lang('pages:draft_label'), 'live'=>lang('pages:live_label')), $page->status, 'id="category_id"') ?>
	</div>

</div>
</div>


<?php if ($this->method == 'create'): ?>
<div class="form-group">
<div class="row">
	
	<label class="col-lg-2" for="navigation_group_id"><?php echo lang('pages:navigation_label') ?></label>

	<div class="col-lg-10">
		<?php echo form_multiselect('navigation_group_id[]', array(lang('global:select-none')) + $navigation_groups, $page->navigation_group_id) ?>
	</div>

</div>
</div>
<?php endif ?>