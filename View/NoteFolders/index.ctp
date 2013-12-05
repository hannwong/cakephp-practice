<div class="noteFolders index">
	<h2><?php echo __('Folders'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('note_folder_id', 'Folder'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id', 'Owner'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($noteFolders as $noteFolder): ?>
	<tr>
		<td><?php echo h($noteFolder['NoteFolder']['id']); ?>&nbsp;</td>
		<td><?php echo h($noteFolder['NoteFolder']['name']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($noteFolder['ParentNoteFolder']['name'], array('controller' => 'note_folders', 'action' => 'view', $noteFolder['ParentNoteFolder']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($noteFolder['User']['username'], array('controller' => 'users', 'action' => 'view', $noteFolder['User']['id'])); ?>
		</td>
		<td><?php echo h($noteFolder['NoteFolder']['created']); ?>&nbsp;</td>
		<td><?php echo h($noteFolder['NoteFolder']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $noteFolder['NoteFolder']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $noteFolder['NoteFolder']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $noteFolder['NoteFolder']['id']), null, __('Are you sure you want to delete # %s?', $noteFolder['NoteFolder']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Folder'), array('action' => 'add')); ?></li>
	</ul>
	<div class="top-level-menu">
	<p>Top-level Menu</p>
	<ul>
		<li><?php echo $this->Html->link(__('List Notes'), array('controller' => 'notes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
	</ul>
</div>
