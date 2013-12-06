<div class="notes view">
<h2><?php echo __('Note'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($note['Note']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Owner'); ?></dt>
		<dd>
			<?php echo $this->Html->link($note['User']['username'], array('controller' => 'users', 'action' => 'view', $note['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Title'); ?></dt>
		<dd>
			<?php echo h($note['Note']['title']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Body'); ?></dt>
		<dd>
			<?php echo h($note['Note']['body']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Folder'); ?></dt>
		<dd>
			<?php echo $this->Html->link($note['NoteFolder']['name'], array('controller' => 'note_folders', 'action' => 'view', $note['NoteFolder']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($note['Note']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($note['Note']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('Edit Note'), array('action' => 'edit', $note['Note']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Note'), array('action' => 'delete', $note['Note']['id']), null, __('Are you sure you want to delete # %s?', $note['Note']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('New Note'), array('action' => 'add')); ?></li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Group Permissions'); ?></h3>
	<h4><?php echo __('(CRUD. -1 deny; 0 inherit; 1 allow)'); ?></h4>
	<?php if (!empty($note['groupPerms'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Create'); ?></th>
		<th><?php echo __('Read'); ?></th>
		<th><?php echo __('Update'); ?></th>
		<th><?php echo __('Delete'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($note['groupPerms'] as $perm): ?>
		<tr>
			<td><?php echo $perm['Group']['id']; ?></td>
			<td><?php echo $perm['Group']['name']; ?></td>
			<td><?php echo $perm['Permission']['_create']; ?></td>
			<td><?php echo $perm['Permission']['_read']; ?></td>
			<td><?php echo $perm['Permission']['_update']; ?></td>
			<td><?php echo $perm['Permission']['_delete']; ?></td>
			<td class="actions">
				<?php echo __('Coming Soon!'); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li>New Group Permission (Coming Soon!)</li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('User Permissions'); ?></h3>
	<h4><?php echo __('(CRUD. -1 deny; 0 inherit; 1 allow)'); ?></h4>
	<?php if (!empty($note['userPerms'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Create'); ?></th>
		<th><?php echo __('Read'); ?></th>
		<th><?php echo __('Update'); ?></th>
		<th><?php echo __('Delete'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($note['userPerms'] as $perm): ?>
		<tr>
			<td><?php echo $perm['User']['id']; ?></td>
			<td><?php echo $perm['User']['username']; ?></td>
			<td><?php echo $perm['Permission']['_create']; ?></td>
			<td><?php echo $perm['Permission']['_read']; ?></td>
			<td><?php echo $perm['Permission']['_update']; ?></td>
			<td><?php echo $perm['Permission']['_delete']; ?></td>
			<td class="actions">
				<?php echo __('Coming Soon!'); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li>New User Permission (Coming Soon!)</li>
		</ul>
	</div>
</div>
