<?php
/**
 * Printer 主题 - Schema.org 结构化数据子系统
 *
 * 由 functions.php 顶部 require_once 加载。
 * 在首页输出 WebSite/Blog/SearchAction，在文章页输出 BlogPosting 的 JSON-LD。
 * 由 header.php 通过 printerPaperRenderStructuredData($this) 调用。
 *
 * @package Printer
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function printerPaperPostTitle($archive) {
  if ($archive && isset($archive->title)) {
    return printerPaperPlainText($archive->title, 110);
  }

  if ($archive && method_exists($archive, 'title')) {
    ob_start();
    $archive->title();
    return printerPaperPlainText(ob_get_clean(), 110);
  }

  return '';
}

function printerPaperPostText($archive) {
  if ($archive && isset($archive->text)) {
    return (string) $archive->text;
  }

  if ($archive && isset($archive->content)) {
    return (string) $archive->content;
  }

  return '';
}

function printerPaperPostAuthorName($archive, $fallback = '') {
  if ($archive && isset($archive->author) && is_object($archive->author)) {
    if (isset($archive->author->screenName) && trim((string) $archive->author->screenName) !== '') {
      return printerPaperPlainText($archive->author->screenName, 80);
    }
    if (isset($archive->author->name) && trim((string) $archive->author->name) !== '') {
      return printerPaperPlainText($archive->author->name, 80);
    }
  }

  if ($archive && method_exists($archive, 'author')) {
    ob_start();
    $archive->author();
    $author = printerPaperPlainText(ob_get_clean(), 80);
    if ($author !== '') {
      return $author;
    }
  }

  return printerPaperPlainText($fallback, 80);
}

function printerPaperPostCategories($archive) {
  if (!$archive || !method_exists($archive, 'category')) {
    return array();
  }

  ob_start();
  $archive->category(',');
  $categoryText = printerPaperPlainText(ob_get_clean());
  if ($categoryText === '') {
    return array();
  }

  $items = array();
  foreach (explode(',', $categoryText) as $category) {
    $category = trim($category);
    if ($category !== '') {
      $items[] = $category;
    }
  }

  return array_values(array_unique($items));
}

function printerPaperFirstImageUrl($html, $siteUrl = '') {
  if (!preg_match('/<img\b[^>]*\bsrc=["\']([^"\']+)["\']/i', (string) $html, $matches)) {
    return '';
  }

  return printerPaperAbsoluteUrl($matches[1], $siteUrl);
}

function printerPaperSchemaPublisher($options, $siteUrl = '') {
  $publisherName = printerPaperPlainText(printerPaperOptionValue($options, 'schemaPublisherName'));
  if ($publisherName === '') {
    $publisherName = printerPaperPlainText(printerPaperOptionValue($options, 'logoText'));
  }
  if ($publisherName === '') {
    $publisherName = printerPaperPlainText(printerPaperOptionValue($options, 'title'));
  }

  $logoUrl = printerPaperOptionValue($options, 'schemaPublisherLogo');
  if (trim((string) $logoUrl) === '') {
    $logoUrl = printerPaperOptionValue($options, 'logoUrl');
  }
  $logoUrl = printerPaperAbsoluteUrl($logoUrl, $siteUrl);

  $publisher = array(
    '@type' => 'Organization',
    'name' => $publisherName
  );

  if ($logoUrl !== '') {
    $publisher['logo'] = array(
      '@type' => 'ImageObject',
      'url' => $logoUrl
    );
  }

  return $publisher;
}

function printerPaperSchemaSameAs($options) {
  $links = array(
    printerPaperOptionValue($options, 'socialGithub'),
    printerPaperOptionValue($options, 'socialTwitter'),
    printerPaperOptionValue($options, 'socialWeibo')
  );

  $sameAs = array();
  foreach ($links as $link) {
    $link = trim((string) $link);
    if ($link !== '' && preg_match('/^https?:\/\//i', $link)) {
      $sameAs[] = $link;
    }
  }

  return array_values(array_unique($sameAs));
}

function printerPaperCleanSchemaData($data) {
  if (!is_array($data)) {
    return $data;
  }

  $clean = array();
  foreach ($data as $key => $value) {
    if (is_array($value)) {
      $value = printerPaperCleanSchemaData($value);
      if (empty($value)) {
        continue;
      }
    } elseif ($value === null || $value === '') {
      continue;
    }
    $clean[$key] = $value;
  }

  return $clean;
}

function printerPaperBuildHomeSchema($archive, $options, $siteUrl, $siteName, $description, $publisher) {
  $sameAs = printerPaperSchemaSameAs($options);
  $website = array(
    '@type' => 'WebSite',
    '@id' => rtrim($siteUrl, '/') . '/#website',
    'url' => $siteUrl,
    'name' => $siteName,
    'description' => $description,
    'inLanguage' => 'zh-CN',
    'publisher' => array('@id' => rtrim($siteUrl, '/') . '/#publisher'),
    'potentialAction' => array(
      '@type' => 'SearchAction',
      'target' => rtrim($siteUrl, '/') . '/?s={search_term_string}',
      'query-input' => 'required name=search_term_string'
    )
  );

  if (!empty($sameAs)) {
    $website['sameAs'] = $sameAs;
  }

  $publisher['@id'] = rtrim($siteUrl, '/') . '/#publisher';

  return array(
    '@context' => 'https://schema.org',
    '@graph' => array(
      $publisher,
      $website,
      array(
        '@type' => 'Blog',
        '@id' => rtrim($siteUrl, '/') . '/#blog',
        'url' => $siteUrl,
        'name' => $siteName,
        'description' => $description,
        'inLanguage' => 'zh-CN',
        'publisher' => array('@id' => rtrim($siteUrl, '/') . '/#publisher'),
        'isPartOf' => array('@id' => rtrim($siteUrl, '/') . '/#website')
      )
    )
  );
}

function printerPaperBuildPostSchema($archive, $options, $siteUrl, $siteName, $description, $publisher) {
  $postUrl = printerPaperCurrentUrl($archive, $siteUrl);
  $postText = printerPaperPostText($archive);
  $postDescription = printerPaperPlainText($postText, 180);
  if ($postDescription === '') {
    $postDescription = $description;
  }

  $created = isset($archive->created) ? (int) $archive->created : 0;
  $modified = isset($archive->modified) ? (int) $archive->modified : 0;
  if ($modified <= 0) {
    $modified = $created;
  }

  $imageUrl = printerPaperFirstImageUrl($postText, $siteUrl);
  if ($imageUrl === '') {
    $imageUrl = printerPaperAbsoluteUrl(printerPaperOptionValue($options, 'logoUrl'), $siteUrl);
  }

  $categories = printerPaperPostCategories($archive);
  $schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    '@id' => rtrim($postUrl, '/') . '#blogposting',
    'mainEntityOfPage' => array(
      '@type' => 'WebPage',
      '@id' => $postUrl
    ),
    'headline' => printerPaperPostTitle($archive),
    'description' => $postDescription,
    'url' => $postUrl,
    'inLanguage' => 'zh-CN',
    'isPartOf' => array(
      '@type' => 'Blog',
      'name' => $siteName,
      'url' => $siteUrl
    ),
    'author' => array(
      '@type' => 'Person',
      'name' => printerPaperPostAuthorName($archive, $siteName)
    ),
    'publisher' => $publisher,
    'image' => $imageUrl,
    'datePublished' => $created > 0 ? date('c', $created) : '',
    'dateModified' => $modified > 0 ? date('c', $modified) : '',
    'articleSection' => $categories,
    'keywords' => $categories
  );

  return printerPaperCleanSchemaData($schema);
}

function printerPaperBuildStructuredData($archive) {
  if (!$archive) {
    return array();
  }

  $options = printerPaperGetOptions(isset($archive->options) ? $archive->options : null);
  if (!$options) {
    return array();
  }

  if ((string) printerPaperOptionValue($options, 'enableStructuredData', '1') === '0') {
    return array();
  }

  $siteUrl = printerPaperAbsoluteUrl(printerPaperOptionValue($options, 'siteUrl'), printerPaperOptionValue($options, 'siteUrl'));
  if ($siteUrl === '') {
    $siteUrl = printerPaperOptionValue($options, 'siteUrl');
  }

  $siteName = printerPaperPlainText(printerPaperOptionValue($options, 'logoText'));
  if ($siteName === '') {
    $siteName = printerPaperPlainText(printerPaperOptionValue($options, 'title'));
  }

  $description = printerPaperPlainText(printerPaperOptionValue($options, 'aiSiteSummary'), 240);
  if ($description === '') {
    $description = printerPaperPlainText(printerPaperOptionValue($options, 'subTitle'), 240);
  }
  if ($description === '') {
    $description = printerPaperPlainText(printerPaperOptionValue($options, 'description'), 240);
  }

  $publisher = printerPaperSchemaPublisher($options, $siteUrl);

  if ($archive->is('index')) {
    return printerPaperCleanSchemaData(printerPaperBuildHomeSchema($archive, $options, $siteUrl, $siteName, $description, $publisher));
  }

  if ($archive->is('post')) {
    return printerPaperBuildPostSchema($archive, $options, $siteUrl, $siteName, $description, $publisher);
  }

  return array();
}

function printerPaperRenderStructuredData($archive) {
  $schema = printerPaperBuildStructuredData($archive);
  if (empty($schema)) {
    return;
  }

  $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
  if (defined('JSON_PRETTY_PRINT')) {
    $flags = $flags | JSON_PRETTY_PRINT;
  }

  $json = json_encode($schema, $flags);
  if ($json === false || $json === '') {
    return;
  }

  echo "\n  <script type=\"application/ld+json\">\n" . $json . "\n  </script>\n";
}
