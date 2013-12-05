<div class="notes form">
<?php echo $this->Form->create('Note'); ?>
	<fieldset>
		<legend><?php echo __('Add Note'); ?></legend>
	<?php
		echo $this->Form->input('title');
		echo $this->Form->input('body');
		echo $this->Form->input('note_folder_id', array('label' => 'Folder'));
		echo $this->Form->input('user_id', array('label' => 'Owner (username)'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back'), array('action' => 'index')); ?></li>
	</ul>
</div>
