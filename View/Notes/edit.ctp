<div class="notes form">
<?php echo $this->Form->create('Note'); ?>
	<fieldset>
		<legend><?php echo __('Edit Note'); ?></legend>
	<?php
		echo $this->Form->input('id');
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
		<li><?php echo $this->Form->postLink(__('Delete Note'), array('action' => 'delete', $this->Form->value('Note.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Note.id'))); ?></li>
		<li><?php echo $this->Html->link(__('New Note'), array('action' => 'add')); ?></li>
	</ul>
</div>
