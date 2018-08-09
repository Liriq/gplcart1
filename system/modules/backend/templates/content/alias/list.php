<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($aliases) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('alias_delete')) { ?>
  <div class="form-inline actions">
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
      </select>
        <button class="btn btn-secondary hidden-js" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
    </div>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table aliases">
      <thead class="thead-light">
        <tr>
          <th>
            <input type="checkbox" onchange="Gplcart.selectAll(this);">
          </th>
          <th>
            <a href="<?php echo $sort_alias_id; ?>">
              <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_alias; ?>">
              <?php echo $this->text('Alias'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_entity; ?>">
              <?php echo $this->text('Entity'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_entity_id; ?>">
              <?php echo $this->text('Entity ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" name="alias_like" value="<?php echo $filter_alias_like; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select name="entity" class="form-control">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach (array_keys($handlers) as $entity) { ?>
              <option value="<?php echo $this->e($entity); ?>"<?php echo $filter_entity == $entity ? ' selected' : '' ?>>
                <?php echo $this->text(ucfirst($entity)); ?>
              </option>
              <?php } ?>
            </select>
          </th>
          <th>
            <input class="form-control" name="entity_id" value="<?php echo $filter_entity_id; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <a href="<?php echo $this->url($_path); ?>" class="btn clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
              <i class="fa fa-sync"></i>
            </a>
            <button class="btn btn-secondary filter" title="<?php echo $this->text('Filter'); ?>">
              <i class="fa fa-search"></i>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if ($_filtering && empty($aliases)) { ?>
        <tr>
          <td colspan="6">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($aliases as $id => $alias) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>">
          </td>
          <td class="middle">
            <?php echo $this->e($id); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($alias['alias']); ?>
          </td>
          <td class="middle">
            <?php echo $this->text(ucfirst($alias['entity'])); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($alias['entity_id']); ?>
          </td>
          <td></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php if (!empty($_pager)) { ?>
    <?php echo $_pager; ?>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } ?>
