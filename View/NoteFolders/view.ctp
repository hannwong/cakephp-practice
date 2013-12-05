<div class="noteFolders view">
<h2><?php echo __('Folder'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($noteFolder['NoteFolder']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($noteFolder['NoteFolder']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Parent Folder'); ?></dt>
		<dd>
			<?php echo $this->Html->link($noteFolder['ParentNoteFolder']['name'], array('controller' => 'note_folders', 'action' => 'view', $noteFolder['ParentNoteFolder']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Owner'); ?></dt>
		<dd>
			<?php echo $this->Html->link($noteFolder['User']['username'], array('controller' => 'users', 'action' => 'view', $noteFolder['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($noteFolder['NoteFolder']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($noteFolder['NoteFolder']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('Edit Folder'), array('action' => 'edit', $noteFolder['NoteFolder']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Folder'), array('action' => 'delete', $noteFolder['NoteFolder']['id']), null, __('Are you sure you want to delete # %s?', $noteFolder['NoteFolder']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('New Folder'), array('action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Note Folders'); ?></h3>
	<?php if (!empty($noteFolder['ChildNoteFolder'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Folder Id'); ?></th>
		<th><?php echo __('User Id'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($noteFolder['ChildNoteFolder'] as $childNoteFolder): ?>
		<tr>
			<td><?php echo $childNoteFolder['id']; ?></td>
			<td><?php echo $childNoteFolder['name']; ?></td>
			<td><?php echo $childNoteFolder['note_folder_id']; ?></td>
			<td><?php echo $childNoteFolder['user_id']; ?></td>
			<td><?php echo $childNoteFolder['created']; ?></td>
			<td><?php echo $childNoteFolder['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'note_folders', 'action' => 'view', $childNoteFolder['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'note_folders', 'action' => 'edit', $childNoteFolder['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'note_folders', 'action' => 'delete', $childNoteFolder['id']), null, __('Are you sure you want to delete # %s?', $childNoteFolder['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Child Note Folder'), array('controller' => 'note_folders', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Notes'); ?></h3>
	<?php if (!empty($noteFolder['Note'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('User Id'); ?></th>
		<th><?php echo __('Title'); ?></th>
		<th><?php echo __('Body'); ?></th>
		<th><?php echo __('Folder Id'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($noteFolder['Note'] as $note): ?>
		<tr>
			<td><?php echo $note['id']; ?></td>
			<td><?php echo $note['user_id']; ?></td>
			<td><?php echo $note['title']; ?></td>
			<td><?php echo $note['body']; ?></td>
			<td><?php echo $note['note_folder_id']; ?></td>
			<td><?php echo $note['created']; ?></td>
			<td><?php echo $note['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'notes', 'action' => 'view', $note['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'notes', 'action' => 'edit', $note['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'notes', 'action' => 'delete', $note['id']), null, __('Are you sure you want to delete # %s?', $note['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Note'), array('controller' => 'notes', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
