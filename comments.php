<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php if ($this->allow('comment') || $this->commentsNum > 0): ?>
  <section class="comments-area">
    <h3 class="comments-title"><?php $this->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></h3>

    <?php $this->comments()->to($comments); ?>
    <?php if ($comments->have()): ?>
      <ol class="comment-list">
        <?php while ($comments->next()): ?>
          <li id="comment-<?php $comments->theId(); ?>" class="comment-item">
            <p class="comment-meta">
              <span class="comment-author"><?php $comments->author(); ?></span>
              <span class="comment-dot">·</span>
              <time datetime="<?php $comments->date('c'); ?>"><?php $comments->date('Y-m-d H:i'); ?></time>
            </p>
            <div class="comment-content"><?php $comments->content(); ?></div>
          </li>
        <?php endwhile; ?>
      </ol>
      <?php $comments->pageNav(_t('上一页'), _t('下一页'), 2, '...'); ?>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
      <div id="<?php $this->respondId(); ?>" class="respond-form">
        <h4 class="respond-title"><?php _t('发表评论'); ?></h4>
        <form method="post" action="<?php $this->commentUrl() ?>" id="comment-form">
          <?php if ($this->user->hasLogin()): ?>
            <p class="respond-login">
              <?php _t('已登录为'); ?>
              <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
              <a href="<?php $this->options->logoutUrl(); ?>"><?php _t('退出'); ?></a>
            </p>
          <?php else: ?>
            <div class="respond-grid">
              <input type="text" name="author" id="author" placeholder="<?php _t('称呼 *'); ?>" value="<?php $this->remember('author'); ?>" required>
              <input type="email" name="mail" id="mail" placeholder="<?php _t('邮箱 *'); ?>" value="<?php $this->remember('mail'); ?>" required>
              <input type="url" name="url" id="url" placeholder="<?php _t('网站'); ?>" value="<?php $this->remember('url'); ?>">
            </div>
          <?php endif; ?>

          <textarea rows="5" cols="50" name="text" id="textarea" placeholder="<?php _t('写下你的评论...'); ?>" required></textarea>
          <button type="submit"><?php _t('提交评论'); ?></button>
        </form>
      </div>
    <?php else: ?>
      <p class="respond-closed"><?php _t('评论已关闭'); ?></p>
    <?php endif; ?>
  </section>
<?php endif; ?>
