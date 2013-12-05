<div class="noteFolders form">
<?php echo $this->Form->create('NoteFolder'); ?>
	<fieldset>
		<legend><?php echo __('Edit Note Folder'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('note_folder_id');
		echo $this->Form->input('user_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('NoteFolder.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('NoteFolder.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Note Folders'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Note Folders'), array('controller' => 'note_folders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent Note Folder'), array('controller' => 'note_folders', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Notes'), array('controller' => 'notes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Note'), array('controller' => 'notes', 'action' => 'add')); ?> </li>
	</ul>
</div>
