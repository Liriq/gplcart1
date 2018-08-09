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
<?php if($this->access('review')) { ?>
<div class="card">
  <div class="card-header">
    <?php echo $this->text($content['title']); ?>
  </div>
  <div class="card-body">
    <?php if (!empty($content['data'])) { ?>
    <table class="table table-sm">
      <tbody>
        <?php foreach ($content['data'] as $item) { ?>
        <tr>
          <td>
            <?php if($this->access('review_edit')) { ?>
            <a href="<?php echo $this->url("admin/content/review/edit/{$item['review_id']}"); ?>">
              <?php echo $this->truncate($this->e($item['text']), 50); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->truncate($this->e($item['text']), 50); ?>
            <?php } ?>
          </td>
          <td>
            <?php if(empty($item['email'])) { ?>
            <?php echo $this->text('Unknown'); ?>
            <?php } else { ?>
            <?php if($this->access('user_edit')) { ?>
            <a href="<?php echo $this->url("admin/user/edit/{$item['user_id']}"); ?>">
              <?php echo $this->truncate($this->e($item['email']), 30); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->truncate($this->e($item['email']), 50); ?>
            <?php } ?>
            <?php } ?>
          </td>
          <td>
            <?php echo $this->date($item['created']); ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/content/review'); ?>">
        <?php echo $this->text('See all'); ?>
      </a>
    </div>
    <?php } else { ?>
    <?php echo $this->text('There are no items yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>