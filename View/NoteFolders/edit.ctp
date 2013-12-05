<div class="noteFolders form">
<?php echo $this->Form->create('NoteFolder'); ?>
	<fieldset>
		<legend><?php echo __('Edit Folder'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('note_folder_id', array('label' => 'Parent Folder'));
		echo $this->Form->input('user_id', array('label' => 'Owner (username)'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Form->postLink(__('Delete Folder'), array('action' => 'delete', $this->Form->value('NoteFolder.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('NoteFolder.id'))); ?></li>
		<li><?php echo $this->Html->link(__('New Folder'), array('action' => 'add')); ?> </li>
	</ul>
</div>
