<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php if ($this->allow('comment') || $this->commentsNum > 0): ?>
  <section class="comments-area">
    <h3 class="comments-title"><?php $this->commentsNum(_t('还没有评论，来抢沙发吧'), _t('1 条评论'), _t('%d 条评论')); ?></h3>

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
        <h4 class="respond-title"><?php _e('发表评论'); ?></h4>
        <form method="post" action="<?php $this->commentUrl() ?>" id="comment-form">
          <?php if ($this->user->hasLogin()): ?>
            <p class="respond-login">
              <?php _e('当前登录：'); ?>
              <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
              <a href="<?php $this->options->logoutUrl(); ?>"><?php _e('退出登录'); ?></a>
            </p>
          <?php else: ?>
            <div class="respond-grid">
              <div class="respond-group">
                <label for="author" class="respond-label"><?php _e('称呼'); ?> *</label>
                <input type="text" name="author" id="author" placeholder="<?php _e('称呼 *'); ?>" value="<?php $this->remember('author'); ?>" required>
              </div>
              <div class="respond-group">
                <label for="mail" class="respond-label"><?php _e('邮箱'); ?> *</label>
                <input type="email" name="mail" id="mail" placeholder="<?php _e('邮箱 *'); ?>" value="<?php $this->remember('mail'); ?>" required>
              </div>
              <div class="respond-group">
                <label for="url" class="respond-label"><?php _e('网站'); ?></label>
                <input type="url" name="url" id="url" placeholder="<?php _e('网站'); ?>" value="<?php $this->remember('url'); ?>">
              </div>
            </div>
          <?php endif; ?>

          <div class="respond-group">
            <label for="textarea" class="respond-label"><?php _e('评论内容'); ?> *</label>
            <textarea rows="5" cols="50" name="text" id="textarea" placeholder="<?php _e('写下你的评论...'); ?>" required></textarea>
          </div>
          <button type="submit"><?php _e('发布评论'); ?></button>
        </form>
      </div>
    <?php else: ?>
      <p class="respond-closed"><?php _e('本文评论已关闭'); ?></p>
    <?php endif; ?>
  </section>
<?php endif; ?>
