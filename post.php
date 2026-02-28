<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<article>
  <p class="post-meta post-meta-category">
    <span class="post-meta-label"><?php _e('分类'); ?></span>
    <span class="post-meta-value">
      <?php $this->category(''); ?>
    </span>
  </p>
  <p class="post-date"><?php $this->date(); ?></p>
  <h1 class="paper-title"><?php $this->title(); ?></h1>
  <div class="post-excerpt">
    <?php $this->content(); ?>
  </div>
</article>

<nav class="post-nav">
  <div class="post-nav-item post-nav-prev">
    <span class="post-nav-label"><?php _e('上一篇'); ?></span>
    <span class="post-nav-link">
      <?php $this->thePrev('%s', _t('没有更早的文章')); ?>
    </span>
  </div>
  <div class="post-nav-item post-nav-next">
    <span class="post-nav-label"><?php _e('下一篇'); ?></span>
    <span class="post-nav-link">
      <?php $this->theNext('%s', _t('没有更晚的文章')); ?>
    </span>
  </div>
</nav>

<?php $this->need('comments.php'); ?>
<?php $this->need('footer.php'); ?>
