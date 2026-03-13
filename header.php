<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="<?php $this->options->charset(); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <script>
    (function () {
      try {
        var savedMode = localStorage.getItem('printer-theme-mode');
        if (savedMode === 'dark') {
          document.documentElement.classList.add('dark');
        }
      } catch (e) {}
    })();
  </script>
  <title><?php $this->archiveTitle(array(
    'category'  => _t('分类 %s 下的文章'),
    'search'    => _t('包含关键字 %s 的文章'),
    'tag'       => _t('标签 %s 下的文章'),
    'author'    => _t('%s 发布的文章')
  ), '', ' - '); ?><?php $this->options->title(); ?></title>
  <?php if ($this->options->faviconUrl): ?>
    <link rel="icon" href="<?php $this->options->faviconUrl(); ?>">
  <?php endif; ?>
  <link rel="stylesheet" href="<?php $this->options->themeUrl('css/style.css'); ?>">
  <?php
    $cnFontCssUrl = trim((string) $this->options->cnFontCssUrl);
    // 只允许 http/https 协议，防止 javascript: 等伪协议注入
    if ($cnFontCssUrl !== '' && !preg_match('/^https?:\/\//i', $cnFontCssUrl)) {
      $cnFontCssUrl = '';
    }
  ?>
  <?php if ($cnFontCssUrl !== ''): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cnFontCssUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <?php endif; ?>
  <?php
    $cnFontFamily = printerPaperNormalizeFontFamily($this->options->cnFontFamily);
    $cnFontScope = trim((string) $this->options->cnFontScope);
    $cnFontScope = in_array($cnFontScope, array('article', 'paper', 'all'), true) ? $cnFontScope : 'article';
  ?>
  <?php if ($cnFontFamily !== ''): ?>
    <script>
      (function () {
        var family = <?php echo json_encode($cnFontFamily, JSON_UNESCAPED_UNICODE); ?>;
        var scope = <?php echo json_encode($cnFontScope, JSON_UNESCAPED_UNICODE); ?>;
        if (!family) return;
        var root = document.documentElement;
        root.classList.add('printer-cn-font');
        root.classList.add('printer-cn-scope-' + scope);
        root.style.setProperty('--printer-cn-font-family', family);
      })();
    </script>
  <?php endif; ?>
  <?php
    $externalLinkColor = printerPaperNormalizeHexColor($this->options->externalLinkColor);
    $postCategoryColor = printerPaperNormalizeHexColor($this->options->postCategoryColor);
    $postCategoryRgb = printerPaperHexToRgbTriplet($postCategoryColor);
  ?>
  <?php if ($externalLinkColor !== '' || $postCategoryColor !== ''): ?>
    <style>
      :root {
        <?php if ($externalLinkColor !== ''): ?>
        --printer-external-link-color: <?php echo htmlspecialchars($externalLinkColor, ENT_QUOTES, 'UTF-8'); ?>;
        <?php endif; ?>
        <?php if ($postCategoryColor !== ''): ?>
        --printer-post-category-color: <?php echo htmlspecialchars($postCategoryColor, ENT_QUOTES, 'UTF-8'); ?>;
        <?php endif; ?>
        <?php if ($postCategoryRgb !== ''): ?>
        --printer-post-category-rgb: <?php echo htmlspecialchars($postCategoryRgb, ENT_QUOTES, 'UTF-8'); ?>;
        <?php endif; ?>
      }
    </style>
  <?php endif; ?>
  <?php $this->header(); ?>
</head>
<body>
  <div class="site-wrap">
    <header class="printer-top">
      <div class="top-row">
        <a class="brand" href="<?php $this->options->siteUrl(); ?>">
          <?php if ($this->options->logoUrl): ?>
            <span class="brand-logo">
              <img src="<?php $this->options->logoUrl(); ?>" alt="logo">
            </span>
          <?php else: ?>
            <span class="brand-mark"></span>
          <?php endif; ?>
          <span class="brand-text">
            <h1><?php echo $this->options->logoText ? htmlspecialchars($this->options->logoText, ENT_QUOTES, 'UTF-8') : $this->options->title(); ?></h1>
            <p><?php echo $this->options->subTitle ? htmlspecialchars($this->options->subTitle, ENT_QUOTES, 'UTF-8') : $this->options->description(); ?></p>
          </span>
        </a>


        <div class="power">
          <div class="power-dot"></div>
          <span class="power-text power-text-on">ON</span>
          <span class="power-text power-text-off">OFF</span>
        </div>
      </div>

      <div class="menu-row">
        <nav class="menu">
          <a href="<?php $this->options->siteUrl(); ?>" class="<?php if ($this->is('index')): ?>current<?php endif; ?>"><?php _e('主页'); ?></a>
          <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
          <?php while ($pages->next()): ?>
            <a href="<?php $pages->permalink(); ?>" class="<?php if ($this->is('page', $pages->slug)): ?>current<?php endif; ?>"><?php $pages->title(); ?></a>
          <?php endwhile; ?>
        </nav>
        <div class="ctrls">
          <form class="header-search" method="get" action="<?php $this->options->siteUrl(); ?>" role="search">
            <label class="header-search-label" for="header-search-input"><?php _t('搜索'); ?></label>
            <input id="header-search-input" type="search" name="s" placeholder="<?php _t('搜索'); ?>" />
            <button type="submit" class="header-search-btn" aria-label="<?php _t('搜索'); ?>">
              <svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
                <path d="M6.5 2a4.5 4.5 0 1 1 0 9A4.5 4.5 0 0 1 6.5 2zm0 1.5a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"></path>
                <path d="M10.4 10.4a.75.75 0 0 1 1.06 0l2.3 2.3a.75.75 0 1 1-1.06 1.06l-2.3-2.3a.75.75 0 0 1 0-1.06z"></path>
              </svg>
            </button>
          </form>
          <div class="lang">
            <span>EN</span>
            <span class="current">中</span>
          </div>
          <button type="button" class="theme-toggle" id="theme-toggle" aria-label="<?php _e('切换日夜模式'); ?>" data-title-light="<?php _e('切换到日间模式'); ?>" data-title-dark="<?php _e('切换到夜间模式'); ?>"></button>
        </div>
      </div>
    </header>
    <main class="paper">
