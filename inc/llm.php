<?php
/**
 * Printer 主题 - LLM 可读内容子系统
 *
 * 由 functions.php 顶部 require_once 加载。
 * 渲染 /llms.html 独立页面（通过 LLM.php 模板调用 printerPaperRenderLlmIndex）。
 * 注意：根目录静态 /llms.txt 由配套插件 Printerllm 生成，与本文件无关。
 *
 * 配套插件 Printerllm 通过 function_exists 守卫调用这里的函数（LlmRecentPosts /
 * LlmPages / LlmCategories / LlmPostUrl / LlmPageUrl / LlmCategoryUrl / LlmExcerpt /
 * LlmIndexUrl / LlmLimit），并提供自包含 SQL 降级实现。
 *
 * @package Printer
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function printerPaperLlmUrl($url, $siteUrl = '') {
  $url = trim((string) $url);
  if ($url === '') {
    return '';
  }

  return printerPaperAbsoluteUrl($url, $siteUrl);
}

function printerPaperLlmEnabled($options) {
  return (string) printerPaperOptionValue($options, 'enableLlmIndex', '1') !== '0';
}

function printerPaperLlmIndexUrl($options) {
  $siteUrl = printerPaperOptionValue($options, 'siteUrl');
  $url = printerPaperOptionValue($options, 'llmIndexUrl');
  return printerPaperSanitizeLinkUrl($url, $siteUrl, true);
}

function printerPaperLlmLimit($options) {
  $limit = (int) printerPaperOptionValue($options, 'llmRecentPostsLimit', 20);
  if ($limit < 1) {
    return 20;
  }
  if ($limit > 100) {
    return 100;
  }
  return $limit;
}

function printerPaperLlmRecentPosts($limit) {
  $db = Typecho_Db::get();
  $prefix = $db->getPrefix();
  $select = $db->select('cid, title, slug, created, modified, text')
    ->from($prefix . 'contents')
    ->where('type = ?', 'post')
    ->where('status = ?', 'publish')
    ->where('created <= ?', time())
    ->order('created', Typecho_Db::SORT_DESC)
    ->limit($limit);

  return $db->fetchAll($select);
}

function printerPaperLlmPages() {
  $db = Typecho_Db::get();
  $prefix = $db->getPrefix();
  $select = $db->select('cid, title, slug, created, modified, text')
    ->from($prefix . 'contents')
    ->where('type = ?', 'page')
    ->where('status = ?', 'publish')
    ->where('created <= ?', time())
    ->order('order', 'ASC');

  return $db->fetchAll($select);
}

function printerPaperLlmCategories() {
  $db = Typecho_Db::get();
  $prefix = $db->getPrefix();
  $select = $db->select('mid, name, slug, count, description')
    ->from($prefix . 'metas')
    ->where('type = ?', 'category')
    ->order('order', 'ASC');

  return $db->fetchAll($select);
}

function printerPaperWidgetPermalink($widgetName, $params, $request, $options) {
  if (!class_exists('Typecho_Widget')) {
    return '';
  }

  $bufferLevel = ob_get_level();
  try {
    $widget = Typecho_Widget::widget($widgetName, $params, $request);
    if (method_exists($widget, 'have') && method_exists($widget, 'next') && $widget->have()) {
      $widget->next();
    }
    if (isset($widget->permalink) && trim((string) $widget->permalink) !== '') {
      return printerPaperAbsoluteUrl($widget->permalink, printerPaperOptionValue($options, 'siteUrl'));
    }
    if (method_exists($widget, 'permalink')) {
      ob_start();
      $widget->permalink();
      $url = trim(ob_get_clean());
      if ($url !== '') {
        return printerPaperAbsoluteUrl($url, printerPaperOptionValue($options, 'siteUrl'));
      }
    }
  } catch (Exception $e) {
    while (ob_get_level() > $bufferLevel) {
      ob_end_clean();
    }
    return '';
  }

  return '';
}

function printerPaperTypechoRouteUrl($route, $params, $options) {
  if (!class_exists('Typecho_Router')) {
    return '';
  }

  try {
    $index = printerPaperOptionValue($options, 'index');
    $url = Typecho_Router::url($route, $params, $index);
    if (preg_match('/\{[a-zA-Z0-9_]+\}/', (string) $url)) {
      return '';
    }
    return printerPaperAbsoluteUrl($url, printerPaperOptionValue($options, 'siteUrl'));
  } catch (Exception $e) {
    return '';
  }
}

function printerPaperLlmDateRouteParams($created) {
  $created = (int) $created;
  if ($created <= 0) {
    return array();
  }

  return array(
    'year' => date('Y', $created),
    'month' => date('m', $created),
    'day' => date('d', $created)
  );
}

function printerPaperLlmPostUrl($row, $options) {
  $siteUrl = rtrim((string) printerPaperOptionValue($options, 'siteUrl'), '/');
  $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
  $cid = isset($row['cid']) ? (int) $row['cid'] : 0;
  $widgetUrl = printerPaperWidgetPermalink(
    'Widget_Archive@printer_llm_post_' . $cid,
    'pageSize=1&type=post',
    'cid=' . $cid,
    $options
  );

  if ($widgetUrl !== '') {
    return $widgetUrl;
  }

  $routeParams = array_merge(array(
    'cid' => $cid,
    'slug' => $slug
  ), printerPaperLlmDateRouteParams(isset($row['created']) ? $row['created'] : 0));
  $routeUrl = printerPaperTypechoRouteUrl('post', $routeParams, $options);

  if ($routeUrl !== '') {
    return $routeUrl;
  }

  return $siteUrl . '/?p=' . $cid;
}

function printerPaperLlmPageUrl($row, $options) {
  $siteUrl = rtrim((string) printerPaperOptionValue($options, 'siteUrl'), '/');
  $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
  $cid = isset($row['cid']) ? (int) $row['cid'] : 0;
  $widgetUrl = printerPaperWidgetPermalink(
    'Widget_Archive@printer_llm_page_' . $cid,
    'pageSize=1&type=page',
    'cid=' . $cid,
    $options
  );

  if ($widgetUrl !== '') {
    return $widgetUrl;
  }

  $routeUrl = printerPaperTypechoRouteUrl('page', array(
    'slug' => $slug
  ), $options);

  if ($routeUrl !== '') {
    return $routeUrl;
  }

  return $siteUrl . '/?page_id=' . $cid;
}

function printerPaperLlmCategoryUrl($row, $options) {
  $siteUrl = rtrim((string) printerPaperOptionValue($options, 'siteUrl'), '/');
  $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
  $mid = isset($row['mid']) ? (int) $row['mid'] : 0;
  $widgetUrl = '';

  if ($mid > 0 && class_exists('Typecho_Widget')) {
    $bufferLevel = ob_get_level();
    try {
      $categories = Typecho_Widget::widget('Widget_Metas_Category_List@printer_llm_category_list');
      while ($categories->next()) {
        if ((int) $categories->mid === $mid) {
          ob_start();
          $categories->permalink();
          $widgetUrl = trim(ob_get_clean());
          break;
        }
      }
      if ($widgetUrl !== '') {
        return printerPaperAbsoluteUrl($widgetUrl, printerPaperOptionValue($options, 'siteUrl'));
      }
    } catch (Exception $e) {
      while (ob_get_level() > $bufferLevel) {
        ob_end_clean();
      }
    }
  }

  $routeUrl = printerPaperTypechoRouteUrl('category', array(
    'slug' => $slug
  ), $options);

  if ($routeUrl !== '') {
    return $routeUrl;
  }

  if ($slug === '') {
    return $siteUrl . '/';
  }
  return $siteUrl . '/?category=' . rawurlencode($slug);
}

function printerPaperLlmExcerpt($text, $mode = 'summary') {
  if ($mode === 'link') {
    return '';
  }

  $limit = $mode === 'full' ? 600 : 180;
  return printerPaperMarkdownText($text, $limit);
}

function printerPaperRenderLlmIndex($archive) {
  $options = printerPaperGetOptions(isset($archive->options) ? $archive->options : null);
  if (!printerPaperLlmEnabled($options)) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "# LLM index disabled\n\nThis machine-readable index is currently disabled by the site owner.\n";
    return;
  }

  header('Content-Type: text/markdown; charset=UTF-8');

  $siteUrl = rtrim((string) printerPaperOptionValue($options, 'siteUrl'), '/') . '/';
  $siteName = printerPaperMarkdownText(printerPaperOptionValue($options, 'logoText'));
  if ($siteName === '') {
    $siteName = printerPaperMarkdownText(printerPaperOptionValue($options, 'title'));
  }

  $title = printerPaperMarkdownText(printerPaperOptionValue($options, 'llmIndexTitle', 'AI Readable Index'));
  if ($title === '') {
    $title = 'AI Readable Index';
  }

  $summary = printerPaperMarkdownText(printerPaperOptionValue($options, 'aiSiteSummary'), 500);
  if ($summary === '') {
    $summary = printerPaperMarkdownText(printerPaperOptionValue($options, 'description'), 500);
  }

  $feedUrl = printerPaperLlmUrl(printerPaperOptionValue($options, 'llmFeedUrl', '/feed/'), $siteUrl);
  $sitemapUrl = printerPaperLlmUrl(printerPaperOptionValue($options, 'llmSitemapUrl', '/sitemap.xml'), $siteUrl);
  $mode = printerPaperOptionValue($options, 'llmPostOutputMode', 'summary');
  $mode = in_array($mode, array('link', 'summary', 'full'), true) ? $mode : 'summary';

  echo '# ' . $title . "\n\n";
  echo '> Machine-readable index for AI agents and crawlers.' . "\n";
  echo '> Site: ' . $siteName . "\n";
  echo '> Canonical URL: ' . $siteUrl . "\n";
  echo '> Updated: ' . date('c') . "\n\n";

  echo "## Abstract\n\n";
  echo ($summary !== '' ? $summary : 'No site summary has been provided yet.') . "\n\n";

  echo "## How to read this site\n\n";
  $instructions = printerPaperMarkdownMultiline(printerPaperOptionValue($options, 'llmReadInstructions'));
  if (empty($instructions)) {
    $instructions = array(
      'Start with this index to understand the site scope and preferred source order.',
      'Use RSS or Sitemap for discovery, then read individual article URLs for source details.',
      'When citing content, include the original title and canonical URL.',
      'Do not treat navigation labels, random-reading links, comments, or visit statistics as primary content.'
    );
  }
  foreach ($instructions as $idx => $line) {
    echo ($idx + 1) . '. ' . $line . "\n";
  }
  echo "\n";

  $scopeNotes = printerPaperMarkdownMultiline(printerPaperOptionValue($options, 'llmScopeNotes'));
  if (!empty($scopeNotes)) {
    echo "## Good uses\n\n";
    foreach ($scopeNotes as $line) {
      echo '- ' . $line . "\n";
    }
    echo "\n";
  }

  echo "## Recommended sources\n\n";
  echo '- ' . printerPaperMarkdownLink('Home', $siteUrl) . "\n";
  if ($feedUrl !== '') {
    echo '- ' . printerPaperMarkdownLink('RSS', $feedUrl) . "\n";
  }
  if ($sitemapUrl !== '') {
    echo '- ' . printerPaperMarkdownLink('Sitemap', $sitemapUrl) . "\n";
  }
  echo "\n";

  if ((string) printerPaperOptionValue($options, 'llmIncludeCategories', '1') !== '0') {
    echo "## Categories\n\n";
    $categories = printerPaperLlmCategories();
    if (empty($categories)) {
      echo '- No public categories found.' . "\n";
    } else {
      foreach ($categories as $category) {
        $name = printerPaperMarkdownText(isset($category['name']) ? $category['name'] : '');
        $url = printerPaperLlmCategoryUrl($category, $options);
        $count = isset($category['count']) ? (int) $category['count'] : 0;
        $desc = printerPaperMarkdownText(isset($category['description']) ? $category['description'] : '', 140);
        echo '- ' . printerPaperMarkdownLink($name, $url) . ' (' . $count . ' posts)';
        if ($desc !== '') {
          echo ': ' . $desc;
        }
        echo "\n";
      }
    }
    echo "\n";
  }

  echo "## Recent posts\n\n";
  $posts = printerPaperLlmRecentPosts(printerPaperLlmLimit($options));
  if (empty($posts)) {
    echo '- No public posts found.' . "\n";
  } else {
    foreach ($posts as $post) {
      $title = printerPaperMarkdownText(isset($post['title']) ? $post['title'] : '');
      $url = printerPaperLlmPostUrl($post, $options);
      $date = isset($post['created']) ? date('Y-m-d', (int) $post['created']) : '';
      $excerpt = printerPaperLlmExcerpt(isset($post['text']) ? $post['text'] : '', $mode);
      echo '- ' . printerPaperMarkdownLink($title, $url);
      if ($date !== '') {
        echo ' — ' . $date;
      }
      if ($excerpt !== '') {
        echo ': ' . $excerpt;
      }
      echo "\n";
    }
  }
  echo "\n";

  if ((string) printerPaperOptionValue($options, 'llmIncludePages', '1') !== '0') {
    echo "## Pages\n\n";
    $pages = printerPaperLlmPages();
    if (empty($pages)) {
      echo '- No public pages found.' . "\n";
    } else {
      foreach ($pages as $page) {
        $title = printerPaperMarkdownText(isset($page['title']) ? $page['title'] : '');
        $url = printerPaperLlmPageUrl($page, $options);
        $excerpt = printerPaperMarkdownText(isset($page['text']) ? $page['text'] : '', 160);
        echo '- ' . printerPaperMarkdownLink($title, $url);
        if ($excerpt !== '') {
          echo ': ' . $excerpt;
        }
        echo "\n";
      }
    }
    echo "\n";
  }

  echo "## What's not served\n\n";
  $notServed = printerPaperMarkdownMultiline(printerPaperOptionValue($options, 'llmNotServedNotes'));
  if (empty($notServed)) {
    $notServed = array(
      'Admin pages, login screens, and private drafts are not part of this public index.',
      'Comments are not treated as primary factual sources.',
      'Dynamic visit statistics, navigation labels, and random-reading controls are not content sources.',
      'Search result pages and pagination pages should be used only for discovery, not citation.'
    );
  }
  foreach ($notServed as $line) {
    echo '- ' . $line . "\n";
  }
  echo "\n";

  echo "## Citation / Usage\n\n";
  $citation = printerPaperMarkdownMultiline(printerPaperOptionValue($options, 'llmCitationNotes'));
  if (empty($citation)) {
    $citation = array('When using or citing this site, include the article title and original URL.');
  }
  foreach ($citation as $line) {
    echo '- ' . $line . "\n";
  }
}
