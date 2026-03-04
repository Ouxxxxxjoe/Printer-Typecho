<?php
/**
 * 一款仿打印纸风格的 Typecho 主题，复刻自NOOC https://nooc.me/ 
 * @package Printer
 * @author zhinan
 * @version 1.1
 * @link https://zhinan.blog/
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>
<?php $this->need('header.php'); ?>

<h2 class="paper-title">
  <?php $this->archiveTitle(array(
    'category'  => _t('%s'),
    'search'    => _t('搜索：%s'),
    'tag'       => _t('标签：%s'),
    'author'    => _t('%s')
  ), '', ''); ?>
</h2>
<p class="paper-subtitle"><?php _e('共 %d 篇内容', $this->getTotal()); ?></p>

<section class="paper-meta">
  <div class="meta-group">
    <p class="meta-label">
      <span class="meta-label-icon" aria-hidden="true">
        <svg viewBox="0 0 16 16" focusable="false">
          <rect x="2" y="3" width="12" height="10" rx="2" ry="2"></rect>
          <path d="M4 5h8v2H4z"></path>
        </svg>
      </span>
      <?php _e('活动'); ?>
    </p>
    <div class="meta-tags">
      <?php $latestCid = 0; ?>
      <?php $this->widget('Widget_Contents_Post_Recent', 'pageSize=1')->to($latestPost); ?>
      <?php if ($latestPost->have()): $latestPost->next(); ?>
        <?php $latestCid = (int) $latestPost->cid; ?>
        <a href="<?php $latestPost->permalink(); ?>"><?php _e('最新文章'); ?></a>
      <?php else: ?>
        <a href="<?php $this->options->siteUrl(); ?>"><?php _e('最新文章'); ?></a>
      <?php endif; ?>

      <?php $randomCids = printerPaperGetRandomCids(4, $this->options->randomReadCategories); ?>
      <?php if (!empty($randomCids) && $latestCid > 0): ?>
        <?php $filteredRandomCids = array(); ?>
        <?php foreach ($randomCids as $randomCid): ?>
          <?php if ((int) $randomCid !== $latestCid): ?>
            <?php $filteredRandomCids[] = (int) $randomCid; ?>
          <?php endif; ?>
        <?php endforeach; ?>
        <?php $randomCids = $filteredRandomCids; ?>
      <?php endif; ?>

      <?php if (!empty($randomCids)): ?>
        <?php
          $randomCid = (int) $randomCids[0];
          $_randomReadUrl = null;
          try {
            $this->widget('Widget_Archive@random_pick_' . $randomCid, 'pageSize=1&type=post', 'cid=' . $randomCid)->to($randomPost);
            if ($randomPost->have()) {
              $randomPost->next();
              $_randomReadUrl = $randomPost->permalink;
            }
          } catch (Exception $e) {
            // Widget 加载失败时降级为首页链接
          }
        ?>
        <a href="<?php echo $_randomReadUrl ? htmlspecialchars($_randomReadUrl, ENT_QUOTES, 'UTF-8') : $this->options->siteUrl(); ?>"><?php _e('随机阅读'); ?></a>
      <?php else: ?>
        <a href="<?php $this->options->siteUrl(); ?>"><?php _e('随机阅读'); ?></a>
      <?php endif; ?>
    </div>
  </div>
  <div class="meta-group">
    <p class="meta-label">
      <span class="meta-label-icon" aria-hidden="true">
        <svg viewBox="0 0 16 16" focusable="false">
          <path d="M2 4.5A1.5 1.5 0 0 1 3.5 3h3A1.5 1.5 0 0 1 8 4.5V6A1.5 1.5 0 0 1 6.5 7h-3A1.5 1.5 0 0 1 2 5.5z"></path>
          <path d="M2 10.5A1.5 1.5 0 0 1 3.5 9h7A1.5 1.5 0 0 1 12 10.5v1A1.5 1.5 0 0 1 10.5 13h-7A1.5 1.5 0 0 1 2 11.5z"></path>
        </svg>
      </span>
      <?php _e('分类'); ?>
    </p>
    <div class="meta-tags">
      <?php $this->widget('Widget_Metas_Category_List')->to($categories); ?>
      <?php while ($categories->next()): ?>
        <a href="<?php $categories->permalink(); ?>"><?php $categories->name(); ?> (<?php $categories->count(); ?>)</a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<ul class="post-list">
  <?php while ($this->next()): ?>
    <li class="post-item">
      <p class="post-date"><?php $this->date(); ?></p>
      <h3 class="post-title">
        <a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a>
      </h3>
      <p class="post-excerpt"><?php $this->excerpt(80, '...'); ?></p>
    </li>
  <?php endwhile; ?>
</ul>

<?php $this->pageNav('上一页', '下一页', 2, '...'); ?>

<?php $this->need('footer.php'); ?>
