<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<article class="not-found">
  <h1 class="paper-title"><?php _e('页面不存在'); ?></h1>
  <p class="paper-subtitle"><?php _e('你访问的链接可能已被删除、改名，或暂时不可用。'); ?></p>

  <div class="not-found-hint">
    <?php _e('你可以尝试返回上一页、回到首页，或者搜索站内内容。'); ?>
  </div>

  <form class="not-found-search" method="get" action="<?php $this->options->siteUrl(); ?>">
    <label class="not-found-search-label" for="not-found-search-input"><?php _e('搜索'); ?></label>
    <div class="not-found-search-bar">
      <input id="not-found-search-input" type="search" name="s" placeholder="<?php _e('搜索站内内容…'); ?>" />
      <button type="submit" class="not-found-search-submit" aria-label="<?php _e('搜索'); ?>">
        <svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
          <path d="M6.5 2a4.5 4.5 0 1 1 0 9A4.5 4.5 0 0 1 6.5 2zm0 1.5a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"></path>
          <path d="M10.4 10.4a.75.75 0 0 1 1.06 0l2.3 2.3a.75.75 0 1 1-1.06 1.06l-2.3-2.3a.75.75 0 0 1 0-1.06z"></path>
        </svg>
      </button>
    </div>
  </form>

  <section class="paper-meta">
    <div class="meta-group">
      <p class="meta-label"><?php _e('分类'); ?></p>
      <div class="meta-tags">
        <?php $this->widget('Widget_Metas_Category_List')->to($categories); ?>
        <?php $count = 0; ?>
        <?php while ($categories->next() && $count < 12): ?>
          <a href="<?php $categories->permalink(); ?>"><?php $categories->name(); ?></a>
          <?php $count++; ?>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
</article>

<?php $this->need('footer.php'); ?>

