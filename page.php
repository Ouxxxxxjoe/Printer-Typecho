<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<div id="reading-progress" role="progressbar" aria-label="<?php _e('阅读进度'); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>

<article>
  <h1 class="paper-title"><?php $this->title(); ?></h1>
  <div class="post-content">
    <?php $this->content(); ?>
  </div>
</article>

<?php $this->need('comments.php'); ?>
<?php $this->need('footer.php'); ?>
