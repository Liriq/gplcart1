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
<?php if (!empty($items)) { ?>
<?php $depth = 0; ?>
<?php $num_at_depth = 0; ?>
<ul class="navbar-nav">
  <li class="dropdown nav-item">
    <?php foreach ($items as $item) { ?>
    <?php $diffdepth = 0; ?>
    <?php if ($item['depth'] > $depth) { ?>
    <ul class="<?php echo $item['depth'] == 1 ? 'dropdown-menu ' : ''; ?>depth-<?php echo $item['depth']; ?>">
      <li class="nav-item depth-<?php echo $item['depth']; ?>">
        <?php $depth = $item['depth']; ?>
        <?php $num_at_depth = 0; ?>
        <?php } ?>
        <?php if ($item['depth'] < $depth) { ?>
        <?php $diffdepth = $depth - $item['depth']; ?>
        <?php while ($diffdepth > 0) { ?>
      </li>
    </ul>
    <?php $diffdepth--; ?>
    <?php } ?>
    <?php $depth = $item['depth']; ?>
    <?php } ?>
    <?php if (($item['depth'] == $depth) && ($num_at_depth > 0)) { ?>
  </li>
  <li class="nav-item depth-<?php echo $depth; ?><?php echo $depth == 0 ? ' dropdown' : ''; ?>">
    <?php } ?>
    <a href="<?php echo $this->e($item['url']); ?>" class="<?php echo $depth == 0 ? 'nav-link dropdown-toggle' : 'dropdown-item'; ?>"<?php echo $item['depth'] == 0 ? ' data-toggle="dropdown"' : ''; ?>>
      <?php echo $this->e($item['text']); ?>
    </a>
    <?php $num_at_depth++; ?>
    <?php } ?>
  </li>
</ul>
<?php } ?>
</ul>