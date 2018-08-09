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

<?php if($this->access('user')) { ?>
<div class="card">
  <div class="card-header"><?php echo $this->text($content['title']); ?></div>
  <div class="card-body">
    <table class="table table-sm">
      <?php foreach ($content['data'] as $item) { ?>
      <?php if(!$this->isSuperadmin($item['user_id']) || $this->isSuperadmin()) { ?>
      <tr>
        <td>
          <?php if($this->access('user_edit')) { ?>
          <a href="<?php echo $this->url("admin/user/edit/{$item['user_id']}"); ?>">
            <?php echo $this->truncate($this->e($item['email']), 30); ?>
          </a>
          <?php } else { ?>
          <?php echo $this->truncate($this->e($item['email']), 50); ?>
          <?php } ?>
        </td>
        <td><?php echo $this->date($item['created']); ?></td>
      </tr>
      <?php } ?>
      <?php } ?>
    </table>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/user/list'); ?>">
        <?php echo $this->text('See all'); ?>
      </a>
    </div>
  </div>
</div>
<?php } ?>