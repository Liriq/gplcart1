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
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label class="col-md-2 col-form-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-10">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn<?php echo empty($imagestyle['status']) ? '' : ' active'; ?>">
              <input name="imagestyle[status]" type="radio" autocomplete="off" value="1"<?php echo empty($imagestyle['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn<?php echo empty($imagestyle['status']) ? ' active' : ''; ?>">
              <input name="imagestyle[status]" type="radio" autocomplete="off" value="0"<?php echo empty($imagestyle['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="form-text">
            <?php echo $this->text('Disabled image styles will not process images'); ?>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-2 col-form-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-10">
          <input name="imagestyle[name]" class="form-control" maxlength="32" value="<?php echo isset($imagestyle['name']) ? $this->e($imagestyle['name']) : ''; ?>">
          <div class="form-text">
            <?php echo $this->error('name'); ?>
            <div class="text-muted">
              <?php echo $this->text('Descriptive name of the image style for administrators'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('actions', ' has-error'); ?>">
        <label class="col-md-2 col-form-label"><?php echo $this->text('Actions'); ?></label>
        <div class="col-md-10">
          <textarea name="imagestyle[actions]" rows="6" class="form-control"><?php echo $this->e($imagestyle['actions']); ?></textarea>
          <div class="form-text">
            <?php echo $this->error('actions'); ?>
            <div class="text-muted">
              <?php echo $this->text('List of image style actions in format <code>[action ID][whitespace][parameters]</code>. One action per line. Actions will be applied from the top to bottom. For example to make thumbnail 50X50 enter the following code: <code>thumbnail 50,50</code>'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-10 offset-md-2">
          <div class="btn-toolbar">
            <?php if ($can_delete) { ?>
            <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure?'); ?>');">
              <?php echo $this->text('Delete'); ?>
            </button>
            <?php } ?>
            <a class="btn cancel" href="<?php echo $this->url('admin/settings/imagestyle'); ?>">
              <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('image_style_edit') || $this->access('image_style_add')) { ?>
            <button class="btn save" name="save" value="1">
              <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><?php echo $this->text('Legend'); ?></div>
        <div class="card-body">
          <table class="table table-striped table-sm">
            <thead>
              <tr>
                <td><?php echo $this->text('Key'); ?></td>
                <td><?php echo $this->text('Name'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($actions as $action_id => $handler) { ?>
              <tr>
                <td class="middle"><?php echo $this->e($action_id); ?></td>
                <td class="middle"><?php echo $this->text($handler['name']); ?></td>
                <td class="middle">
                  <?php if (isset($handler['description'])) { ?>
                  <?php echo $this->text($handler['description']); ?>
                  <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</form>