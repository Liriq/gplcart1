<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php $show_currency_selector = false; ?>
<?php if (count($_currencies) > 1) { ?>
<?php foreach ($_currencies as $currency) { ?>
<?php if ($_currency['code'] !== $currency['code']) { ?>
<?php $show_currency_selector = true; break; ?>
<?php } ?>
<?php } ?>
<?php } ?>
<?php
$region_content_class = 'col-md-12';
if (!empty($region_left) && empty($region_right)) {
$region_content_class = 'col-md-10';
} else if (empty($region_left) && !empty($region_right)) {
$region_content_class = 'col-md-10';
} else if (!empty($region_left) && !empty($region_right)) {
$region_content_class = 'col-md-8';
}
?>
<body<?php echo $this->attributes(array('class' => $_classes)); ?>>
  <div class="container-fluid">
    <div class="content-wrapper">
      <div class="row">
        <nav class="navbar navbar-default navbar-static-top first">
          <div class="container">
            <div class="navbar-header">
              <p class="navbar-text navbar-hidden-label visible-xs-inline-block">
                <a href="#" class="collapsed" data-toggle="collapse" data-target="#navbar-collapse-first">
                <?php echo $this->text('Account'); ?>
                <?php if(count($_currencies) > 1) { ?>
                ,<?php echo $this->text('Currency'); ?>
                <?php } ?>
                <?php if (count($_languages) > 1) { ?>
                ,<?php echo $this->text('Language'); ?>
                <?php } ?>
                </a>
              </p>
              <p class="navbar-text navbar-hidden-label visible-xs-inline-block">
                <?php if (!empty($_store['data']['phone'][0])) { ?>
                <?php echo $this->text('Call'); ?>: <?php echo $this->e($_store['data']['phone'][0]); ?>
                <?php } ?>
              </p>
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-first">
                <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
            </div>
            <div class="navbar-collapse collapse" id="navbar-collapse-first">
              <ul class="nav navbar-nav">
                <?php if ($show_currency_selector) { ?>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->e($_currency['code']); ?> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <?php foreach ($_currencies as $currency) { ?>
                    <?php if ($_currency['code'] !== $currency['code']) { ?>
                    <li>
                      <a rel="nofollow" href="<?php echo $this->url('', array('currency' => $currency['code'])); ?>"><?php echo $this->e($currency['code']); ?></a>
                    </li>
                    <?php } ?>
                    <?php } ?>
                  </ul>
                </li>
                <?php } else if(count($_currencies) > 1) { ?>
                <li><?php echo $this->e($_currency['name']); ?></li>
                <?php } ?>
                <?php if (count($_language_switcher) > 1) { ?>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?php if (empty($_language_switcher[$_langcode])) { ?>
                    <?php echo $this->text('Select language'); ?>
                    <?php } else { ?>
                    <?php echo $this->e($_language_switcher[$_langcode]['native_name']); ?>
                    <?php } ?>
                    <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                    <?php foreach ($_language_switcher as $language) { ?>
                    <?php if ($language['code'] !== $_langcode) { ?>
                    <li>
                      <a href="<?php echo $this->lurl($language['code'], '', $_query); ?>"><?php echo $this->e($language['native_name']); ?></a>
                    </li>
                    <?php } ?>
                    <?php } ?>
                  </ul>
                </li>
                <?php } ?>
              </ul>
              <?php if (!empty($_store['data']['phone'][0])) { ?>
              <p class="navbar-text navbar-center hidden-xs">
                <?php echo $this->text('Call'); ?>: <?php echo $this->e($_store['data']['phone'][0]); ?>
              </p>
              <?php } ?>
              <ul class="nav navbar-nav navbar-right">
                <?php if ($_is_logged_in) { ?>
                <li>
                  <a href="<?php echo $this->url("account/$_uid"); ?>">
                    <?php echo $this->text('Account'); ?>
                  </a>
                </li>
                <li>
                  <a href="<?php echo $this->url('logout'); ?>">
                    <?php echo $this->text('Log out'); ?>
                  </a>
                </li>
                <?php } else { ?>
                <li>
                  <a rel="nofollow" href="<?php echo $this->url('login'); ?>">
                    <?php echo $this->text('Log in'); ?>
                  </a>
                </li>
                <?php } ?>
              </ul>
            </div>
          </div>
        </nav>
      </div>
      <div class="row">
        <nav class="navbar navbar-unstyled navbar-static-top second">
          <div class="container">
            <?php if (empty($_store_logo)) { ?>
            <a class="navbar-brand hidden-xs" href="<?php echo $this->e($_base); ?>">
              <?php echo $this->e($_store_title); ?>
            </a>
            <?php } else { ?>
            <a class="navbar-logo hidden-xs" href="<?php echo $this->e($_base); ?>">
              <img class="logo" alt="<?php echo $this->e($_store_title); ?>" title="<?php echo $this->e($_store_title); ?>" src="<?php echo $this->e($_store_logo); ?>">
            </a>
            <?php } ?>
            <ul class="nav navbar-nav navbar-right">
              <li class="cart">
                <a rel="nofollow" id="cart-link" href="<?php echo $this->url('checkout'); ?>">
                  <?php echo $this->text('Cart'); ?>
                  <span class="badge" id="cart-quantity"><?php echo $_cart['total']; ?></span>
                </a>
              </li>
              <li class="wishlist">
                <?php if (empty($_wishlist)) { ?>
                <a rel="nofollow" id="wishlist-link" href="<?php echo $this->url('wishlist'); ?>">
                  <?php echo $this->text('Wishlist'); ?>
                  <span class="badge" id="wishlist-quantity">0</span>
                </a>
                <?php } else { ?>
                <a rel="nofollow" id="wishlist-link" href="<?php echo $this->url('wishlist'); ?>">
                  <?php echo $this->text('Wishlist'); ?>
                  <span class="badge" id="wishlist-quantity"><?php echo count($_wishlist); ?></span>
                </a>
                <?php } ?>
              </li>
              <li class="compare">
                <?php if (empty($_comparison)) { ?>
                <a rel="nofollow" id="compare-link" href="<?php echo $this->url('compare'); ?>">
                  <?php echo $this->text('Compare'); ?>
                  <span class="badge" id="compare-quantity">0</span>
                </a>
                <?php } else { ?>
                <a rel="nofollow" id="compare-link" href="<?php echo $this->url('compare'); ?>">
                  <?php echo $this->text('Compare'); ?>
                  <span class="badge" id="compare-quantity">
                    <?php echo count($_comparison); ?>
                  </span>
                </a>
                <?php } ?>
              </li>
            </ul>
            <form class="navbar-form search" action="<?php echo $this->url('search'); ?>">
              <div class="input-group">
                <input type="search" class="form-control" autocomplete="off" name="q" value="<?php echo isset($_query['q']) && $_query['q'] !== '' ? $_query['q'] : ''; ?>" placeholder="<?php echo $this->text('Search'); ?>">
                <span class="input-group-btn">
                  <button class="btn btn-default" data-block-if-empty="q">
                    <i class="fa fa-search"></i>
                  </button>
                </span>
              </div>
            </form>
          </div>
        </nav>
      </div>
      <div class="row">
        <nav class="navbar navbar-inverse navbar-static-top third">
          <div class="container">
            <div class="navbar-header">
              <p class="navbar-text navbar-hidden-label visible-xs-inline-block">
                <a href="#" class="collapsed" data-toggle="collapse" data-target="#navbar-collapse-third">
                  <?php echo $this->text('Categories'); ?>
                </a>
              </p>
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-third">
                <span class="sr-only"><?php echo $this->text('Toggle navigation'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
            </div>
            <div class="navbar-collapse collapse" id="navbar-collapse-third">
              <?php if (!empty($_menu)) { ?>
              <?php echo $_menu; ?>
              <?php } ?>
            </div>
          </div>
        </nav>
      </div>
      <div class="row">
        <div class="container">
          <?php if (!empty($_breadcrumbs) || !empty($_page_title)) { ?>
          <ol class="breadcrumb">
            <?php foreach ($_breadcrumbs as $item) { ?>
            <?php if (empty($item['url'])) { ?>
            <li><?php echo $item['text']; ?></li>
            <?php } else { ?>
            <li><a href="<?php echo $this->e($item['url']); ?>"><?php echo $item['text']; ?></a></li>
            <?php } ?>
            <?php } ?>
            <?php if (!empty($_page_title)) { ?>
            <li><h1><?php echo $_page_title; ?></h1></li>
            <?php } ?>
          </ol>
         <?php } ?>
        </div>
      </div>
      <?php if (!empty($region_top)) { ?>
      <div class="row">
        <div class="col-md-12">
          <div class="region top">
            <?php echo $region_top; ?>
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="container">
        <div class="row main">
          <?php if (!empty($region_left)) { ?>
          <div class="col-md-2">
            <div class="region left"><?php echo $region_left; ?></div>
          </div>
          <?php } ?>
          <div class="<?php echo $region_content_class; ?>">
            <?php if (!empty($_messages)) { ?>
            <div class="row" id="message">
              <div class="col-md-12">
                <?php foreach ($_messages as $type => $strings) { ?>
                <div class="alert alert-<?php echo $this->e($type); ?> alert-dismissible fade in">
                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
                  <?php foreach ($strings as $string) { ?>
                  <?php echo $string; ?><br>
                  <?php } ?>
                </div>
                <?php } ?>
              </div>
            </div>
            <?php } ?>
            <?php if (!empty($region_content)) { ?>
            <div class="region content"><?php echo $region_content; ?></div>
            <?php } ?>
            <?php if (!empty($region_bottom)) { ?>
            <div class="region bottom"><?php echo $region_bottom; ?></div>
            <?php } ?>
          </div>
          <?php if (!empty($region_right)) { ?>
          <div class="col-md-2">
            <div class="region right"><?php echo $region_right; ?></div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="col-md-4">
            <?php if (!empty($_store['data']['owner'])) { ?>
            <?php echo $this->e($_store['data']['owner']); ?>
            <?php } ?>
            <?php if (!empty($_store['data']['phone'])) { ?>
            <h5><?php echo $this->text('Phone'); ?></h5>
            <ul class="list-unstyled">
              <?php foreach ($_store['data']['phone'] as $phone) { ?>
              <li><?php echo $this->e($phone); ?></li>
              <?php } ?>
            </ul>
            <?php } ?>
            <?php if (!empty($_store['data']['fax'])) { ?>
            <h5><?php echo $this->text('Fax'); ?></h5>
            <ul class="list-unstyled">
              <?php foreach ($_store['data']['fax'] as $fax) { ?>
              <li><?php echo $this->e($fax); ?></li>
              <?php } ?>
            </ul>
            <?php } ?>
            <?php if (!empty($_store['data']['email'])) { ?>
            <h5><?php echo $this->text('E-mail'); ?></h5>
            <ul class="list-unstyled">
              <?php foreach ($_store['data']['email'] as $email) { ?>
              <li><?php echo $this->e($email); ?></li>
              <?php } ?>
            </ul>
            <?php } ?>
            <?php if (!empty($_store['data']['address'])) { ?>
            <p><?php echo $this->e($_store['data']['address']); ?></p>
            <?php } ?>
          </div>
          <div class="col-md-4">
            <ul class="list-unstyled">
              <li>
                <a href="<?php echo $this->url('blog'); ?>"><?php echo $this->text('Blog'); ?></a>
              </li>
              <li>
                <a rel="nofollow" href="<?php echo $this->url('page/2'); ?>"><?php echo $this->text('Help'); ?></a>
              </li>
              <li>
                <a rel="nofollow" href="<?php echo $this->url('page/1'); ?>"><?php echo $this->text('Contact'); ?></a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
