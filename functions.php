<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 子系统拆分：访问统计 / LLM 可读内容 / Schema 结构化数据
// 加载发生在所有模板之前，子文件中的函数在模板调用时已就绪。
require_once __DIR__ . '/inc/visit-stats.php';
require_once __DIR__ . '/inc/llm.php';
require_once __DIR__ . '/inc/schema.php';

// Typecho 类声明（用于 IDE 智能提示）
if (false) {
    /** @SuppressWarnings(PHPMD) */
    class Typecho_Widget_Helper_Form {
        public function addItem($item) {}
        public function addInput($input) {}
    }
    /** @SuppressWarnings(PHPMD) */
    class Typecho_Widget_Helper_Layout {
        public function html($html) {}
    }
    /** @SuppressWarnings(PHPMD) */
    class Typecho_Widget_Helper_Form_Element_Text {
        public function __construct($name, $value = null, $default = null, $label = '', $description = '') {}
    }
    /** @SuppressWarnings(PHPMD) */
    class Typecho_Widget_Helper_Form_Element_Textarea {
        public function __construct($name, $value = null, $default = null, $label = '', $description = '') {}
    }
    /** @SuppressWarnings(PHPMD) */
    class Typecho_Widget_Helper_Form_Element_Select {
        public function __construct($name, $options = [], $value = null, $default = null, $label = '', $description = '') {}
    }
    /** @SuppressWarnings(PHPMD) */
    class Typecho_Widget_Helper_Form_Element_Checkbox {
        public function __construct($name, $options = [], $value = null, $default = null, $label = '', $description = '') {}
    }
    /** @SuppressWarnings(PHPMD) */
    class Helper {
        public static function options() {}
    }
    /** @SuppressWarnings(PHPMD) */
    class Typecho_Db {
        const SORT_DESC = 'DESC';
        const LEFT_JOIN = 'LEFT JOIN';
        /**
         * @return static|null
         */
        public static function get() {}
        public function getPrefix() {}
        /**
         * @return \Typecho_Db_Query
         */
        public function select($columns = '*') {}
        public function from($table) {}
        public function where($condition, $value = null) {}
        public function order($field, $direction = self::SORT_DESC) {}
        public function limit($limit) {}
        /**
         * @return \Typecho_Db_Query
         */
        public function join($table, $on, $type = self::LEFT_JOIN) {}
        public function group($field) {}
        /**
         * @return array<int, array<string, mixed>>
         */
        public function fetchAll($select) {}
    }
}

// Typecho 常量声明（用于 IDE 智能提示）
if (!defined('__TYPECHO_ROOT_DIR__')) {
    define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
}

function themeConfig($form) {
  /** @var Typecho_Widget_Helper_Form $form */
  
  // 分组标题样式
  echo '<style>
    .config-group-title {
      background: #f5f5f5;
      padding: 12px 15px;
      margin: 25px 0 15px 0;
      border-left: 4px solid #467b96;
      font-weight: bold;
      font-size: 14px;
      color: #333;
    }
    li:first-child .config-group-title {
      margin-top: 0;
    }
  </style>';

  // ===== 基础设置 =====
  $basicTitle = new Typecho_Widget_Helper_Layout();
  $basicTitle->html('<div class="config-group-title">基础设置</div>');
  $form->addItem($basicTitle);

  $logoUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'logoUrl',
    NULL,
    '',
    _t('Logo 图片'),
    _t('填写图片链接，替换左上角的圆形图标。支持 .png、.jpg、.svg 格式，建议尺寸 100x100 像素')
  );
  $form->addInput($logoUrl);

  $logoText = new Typecho_Widget_Helper_Form_Element_Text(
    'logoText',
    NULL,
    'Zhinan',
    _t('网站名称'),
    _t('显示在 Logo 旁边的文字。留空则使用 Typecho 后台设置的站点标题')
  );
  $form->addInput($logoText);

  $subTitle = new Typecho_Widget_Helper_Form_Element_Text(
    'subTitle',
    NULL,
    '',
    _t('网站副标题'),
    _t('显示在网站名称下方的小字。留空则使用 Typecho 后台设置的站点描述')
  );
  $form->addInput($subTitle);

  $faviconUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'faviconUrl',
    NULL,
    '',
    _t('浏览器标签页图标'),
    _t('显示在浏览器标签页和收藏夹中的图标。支持 .ico、.png 格式，建议尺寸 32x32 像素')
  );
  $form->addInput($faviconUrl);

  $footerCopyright = new Typecho_Widget_Helper_Form_Element_Text(
    'footerCopyright',
    NULL,
    '',
    _t('页脚版权文字'),
    _t('显示在页面底部的版权信息。留空则显示 "© 年份 网站名称"')
  );
  $form->addInput($footerCopyright);

  // ===== 内容设置 =====
  $contentTitle = new Typecho_Widget_Helper_Layout();
  $contentTitle->html('<div class="config-group-title">内容设置</div>');
  $form->addItem($contentTitle);

  $randomReadCategories = new Typecho_Widget_Helper_Form_Element_Text(
    'randomReadCategories',
    NULL,
    '',
    _t('随机阅读范围'),
    _t('限制随机阅读功能只在指定分类中选取文章。填写分类别名，多个分类用英文逗号分隔，如：life,tech。留空则不限制')
  );
  $form->addInput($randomReadCategories);

  $showReadingTime = new Typecho_Widget_Helper_Form_Element_Select(
    'showReadingTime',
    array('1' => _t('显示'), '0' => _t('隐藏')),
    '1',
    _t('阅读时间估算'),
    _t('在文章页面显示预计阅读时长，按每分钟阅读 200 字计算')
  );
  $form->addInput($showReadingTime);

  // ===== AI 可读内容出口 =====
  $aiTitle = new Typecho_Widget_Helper_Layout();
  $aiTitle->html('<div class="config-group-title">AI 可读内容出口</div>');
  $form->addItem($aiTitle);

  $enableStructuredData = new Typecho_Widget_Helper_Form_Element_Select(
    'enableStructuredData',
    array('1' => _t('启用'), '0' => _t('关闭')),
    '1',
    _t('结构化数据 JSON-LD'),
    _t('在首页和文章页输出 Schema.org JSON-LD，帮助搜索引擎和 AI 搜索理解站点内容')
  );
  $form->addInput($enableStructuredData);

  $aiSiteSummary = new Typecho_Widget_Helper_Form_Element_Textarea(
    'aiSiteSummary',
    NULL,
    '',
    _t('站点 AI 摘要'),
    _t('用一两句话说明本站主题、内容范围和适合回答的问题。留空则使用 Typecho 站点描述')
  );
  $form->addInput($aiSiteSummary);

  $schemaPublisherName = new Typecho_Widget_Helper_Form_Element_Text(
    'schemaPublisherName',
    NULL,
    '',
    _t('发布者名称'),
    _t('用于结构化数据中的 publisher。留空则使用网站名称')
  );
  $form->addInput($schemaPublisherName);

  $schemaPublisherLogo = new Typecho_Widget_Helper_Form_Element_Text(
    'schemaPublisherLogo',
    NULL,
    '',
    _t('发布者 Logo'),
    _t('用于结构化数据中的 publisher.logo。留空则使用 Logo 图片配置')
  );
  $form->addInput($schemaPublisherLogo);

  $enableLlmIndex = new Typecho_Widget_Helper_Form_Element_Select(
    'enableLlmIndex',
    array('1' => _t('启用'), '0' => _t('关闭')),
    '1',
    _t('LLM 页面出口'),
    _t('控制 LLM.php 页面模板是否输出机器可读 Markdown 索引')
  );
  $form->addInput($enableLlmIndex);

  $llmIndexTitle = new Typecho_Widget_Helper_Form_Element_Text(
    'llmIndexTitle',
    NULL,
    'AI Readable Index',
    _t('LLM 出口标题'),
    _t('显示在 LLM 页面顶部的标题')
  );
  $form->addInput($llmIndexTitle);

  $llmReadInstructions = new Typecho_Widget_Helper_Form_Element_Textarea(
    'llmReadInstructions',
    NULL,
    '',
    _t('推荐读取说明'),
    _t('告诉 AI 应该如何读取本站内容。留空则使用主题默认说明')
  );
  $form->addInput($llmReadInstructions);

  $llmScopeNotes = new Typecho_Widget_Helper_Form_Element_Textarea(
    'llmScopeNotes',
    NULL,
    '',
    _t('适合回答的问题'),
    _t('说明本站内容适合支持哪些问题或任务')
  );
  $form->addInput($llmScopeNotes);

  $llmNotServedNotes = new Typecho_Widget_Helper_Form_Element_Textarea(
    'llmNotServedNotes',
    NULL,
    '',
    _t('不提供内容说明'),
    _t('说明哪些内容不建议 AI 当作知识来源，如评论区、后台页面、动态统计等')
  );
  $form->addInput($llmNotServedNotes);

  $llmCitationNotes = new Typecho_Widget_Helper_Form_Element_Textarea(
    'llmCitationNotes',
    NULL,
    '',
    _t('引用说明'),
    _t('说明 AI 或用户引用本站内容时应如何署名和附链接')
  );
  $form->addInput($llmCitationNotes);

  $llmRecentPostsLimit = new Typecho_Widget_Helper_Form_Element_Text(
    'llmRecentPostsLimit',
    NULL,
    '20',
    _t('最近文章数量'),
    _t('LLM 页面输出的最近文章数量，建议 10-50')
  );
  $form->addInput($llmRecentPostsLimit);

  $llmPostOutputMode = new Typecho_Widget_Helper_Form_Element_Select(
    'llmPostOutputMode',
    array(
      'summary' => _t('标题、链接和摘要'),
      'link' => _t('仅标题和链接'),
      'full' => _t('包含正文摘录')
    ),
    'summary',
    _t('文章输出模式'),
    _t('控制 LLM 页面中文章列表的详细程度。全文摘录会增加页面体积')
  );
  $form->addInput($llmPostOutputMode);

  $llmIncludeCategories = new Typecho_Widget_Helper_Form_Element_Select(
    'llmIncludeCategories',
    array('1' => _t('包含'), '0' => _t('不包含')),
    '1',
    _t('包含分类列表'),
    _t('在 LLM 页面输出分类入口')
  );
  $form->addInput($llmIncludeCategories);

  $llmIncludePages = new Typecho_Widget_Helper_Form_Element_Select(
    'llmIncludePages',
    array('1' => _t('包含'), '0' => _t('不包含')),
    '1',
    _t('包含独立页面'),
    _t('在 LLM 页面输出 Typecho 独立页面入口')
  );
  $form->addInput($llmIncludePages);

  $llmFeedUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'llmFeedUrl',
    NULL,
    '/feed/',
    _t('RSS 地址'),
    _t('输出到 LLM 页面的 RSS 地址，留空则不显示')
  );
  $form->addInput($llmFeedUrl);

  $llmSitemapUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'llmSitemapUrl',
    NULL,
    '/sitemap.xml',
    _t('站点地图地址'),
    _t('输出到 LLM 页面的 Sitemap 地址，留空则不显示')
  );
  $form->addInput($llmSitemapUrl);

  // ===== 外观样式 =====
  $styleTitle = new Typecho_Widget_Helper_Layout();
  $styleTitle->html('<div class="config-group-title">外观样式</div>');
  $form->addItem($styleTitle);

  $cnFontCssUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'cnFontCssUrl',
    NULL,
    '',
    _t('中文字体 CSS 链接'),
    _t('使用中文网字计划等第三方字体服务时，粘贴提供的 CSS 链接。留空则使用系统默认字体')
  );
  $form->addInput($cnFontCssUrl);

  $cnFontFamily = new Typecho_Widget_Helper_Form_Element_Text(
    'cnFontFamily',
    NULL,
    '',
    _t('中文字体名称'),
    _t('填写 CSS 中定义的字体名称，必须与 CSS 链接中的定义一致。如：LXGW WenKai 或 "Source Han Serif"')
  );
  $form->addInput($cnFontFamily);

  $cnFontScope = new Typecho_Widget_Helper_Form_Element_Select(
    'cnFontScope',
    array(
      'article' => _t('仅文章内容（推荐）'),
      'paper'   => _t('纸张区域（含列表页）'),
      'all'     => _t('全站所有文字')
    ),
    'article',
    _t('字体应用范围'),
    _t('选择自定义字体应用到哪些区域。仅文章内容可保持最佳阅读体验，全站应用可能影响页面加载速度')
  );
  $form->addInput($cnFontScope);

  $externalLinkColor = new Typecho_Widget_Helper_Form_Element_Text(
    'externalLinkColor',
    NULL,
    '#ff6b35',
    _t('外部链接颜色'),
    _t('文章中指向外部网站的链接显示的颜色。支持十六进制，如 #ff6b35 或简写 #f63')
  );
  $form->addInput($externalLinkColor);

  $postCategoryColor = new Typecho_Widget_Helper_Form_Element_Text(
    'postCategoryColor',
    NULL,
    '#ff6b35',
    _t('文章分类颜色'),
    _t('文章页面顶部分类标签的背景色。支持十六进制，如 #ff6b35 或简写 #f63')
  );
  $form->addInput($postCategoryColor);

  $customBgUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'customBgUrl',
    NULL,
    '',
    _t('自定义背景图'),
    _t('填写背景图片链接，支持 .png、.jpg、.webp 等格式。留空则使用默认背景色。建议尺寸 1920x1080 或更大')
  );
  $form->addInput($customBgUrl);

  $customBgDarkOverlay = new Typecho_Widget_Helper_Form_Element_Text(
    'customBgDarkOverlay',
    NULL,
    '0.65',
    _t('黑暗模式背景压暗强度'),
    _t('黑暗模式下背景图的遮罩透明度，范围 0-1。默认 0.65，数值越大背景越暗。0 表示不压暗，1 表示完全黑色')
  );
  $form->addInput($customBgDarkOverlay);

  // ===== 统计与追踪 =====
  $analyticsTitle = new Typecho_Widget_Helper_Layout();
  $analyticsTitle->html('<div class="config-group-title">统计与追踪</div>');
  $form->addItem($analyticsTitle);

  $analyticsCode = new Typecho_Widget_Helper_Form_Element_Textarea(
    'analyticsCode',
    NULL,
    '',
    _t('网站统计代码'),
    _t('粘贴第三方统计平台的追踪代码（如 Google Analytics、百度统计），代码将插入到页面底部')
  );
  $form->addInput($analyticsCode);

  $enableVisitStats = new Typecho_Widget_Helper_Form_Element_Select(
    'enableVisitStats',
    array('1' => _t('启用'), '0' => _t('关闭')),
    '1',
    _t('访问统计'),
    _t('控制页脚总访问 / 今日访问统计。关闭后不会创建统计表、启动 Session 或写入统计 Cookie')
  );
  $form->addInput($enableVisitStats);

  $siteVisits = new Typecho_Widget_Helper_Form_Element_Text(
    'siteVisits',
    NULL,
    '',
    _t('初始总访问量'),
    _t('设置一个起始值，新访问会自动累加。适用于从其他平台迁移过来的网站，留空则从 0 开始统计')
  );
  $form->addInput($siteVisits);

  $todayVisits = new Typecho_Widget_Helper_Form_Element_Text(
    'todayVisits',
    NULL,
    '',
    _t('今日初始访问量'),
    _t('设置今日访问的起始值，新访问会自动累加。每天零点自动清零，留空则从 0 开始')
  );
  $form->addInput($todayVisits);

  $visitIncrement = new Typecho_Widget_Helper_Form_Element_Text(
    'visitIncrement',
    NULL,
    '1',
    _t('访问计数增量'),
    _t('每次访问时增加的数值。默认为 1，如需加速增长可设为更大的数字')
  );
  $form->addInput($visitIncrement);

  // ===== 社交链接 =====
  $socialTitle = new Typecho_Widget_Helper_Layout();
  $socialTitle->html('<div class="config-group-title">社交链接</div>');
  $form->addItem($socialTitle);

  $socialGithub = new Typecho_Widget_Helper_Form_Element_Text(
    'socialGithub',
    NULL,
    '',
    _t('GitHub'),
    _t('填写 GitHub 个人主页链接，留空则不显示')
  );
  $form->addInput($socialGithub);

  $socialTwitter = new Typecho_Widget_Helper_Form_Element_Text(
    'socialTwitter',
    NULL,
    '',
    _t('Twitter / X'),
    _t('填写 Twitter 个人主页链接，留空则不显示')
  );
  $form->addInput($socialTwitter);

  $socialWeibo = new Typecho_Widget_Helper_Form_Element_Text(
    'socialWeibo',
    NULL,
    '',
    _t('微博'),
    _t('填写微博个人主页链接，留空则不显示')
  );
  $form->addInput($socialWeibo);

  $socialEmail = new Typecho_Widget_Helper_Form_Element_Text(
    'socialEmail',
    NULL,
    '',
    _t('邮箱'),
    _t('填写联系邮箱地址，留空则不显示')
  );
  $form->addInput($socialEmail);

  $socialRss = new Typecho_Widget_Helper_Form_Element_Text(
    'socialRss',
    NULL,
    '',
    _t('RSS 订阅地址'),
    _t('填写 RSS 订阅地址（如 /feed/ 或 https://example.com/rss.xml），留空则不显示 RSS 图标')
  );
  $form->addInput($socialRss);

  $llmIndexUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'llmIndexUrl',
    NULL,
    '/llms.txt',
    _t('LLM 页面地址'),
    _t('填写当前站点的 LLM 内容出口地址，支持站内相对地址或完整 URL。默认 /llms.txt，主题会在该路径输出 text/markdown')
  );
  $form->addInput($llmIndexUrl);
}

function printerPaperOptionValue($options, $name, $default = '') {
  if (!$options) {
    return $default;
  }

  if (is_array($options) && array_key_exists($name, $options)) {
    return $options[$name] === null ? $default : $options[$name];
  }

  if ($options instanceof ArrayAccess && isset($options[$name])) {
    return $options[$name] === null ? $default : $options[$name];
  }

  if (is_object($options)) {
    if (isset($options->$name)) {
      return $options->$name === null ? $default : $options->$name;
    }

    if (method_exists($options, '__get')) {
      try {
        $value = $options->__get($name);
        return $value === null ? $default : $value;
      } catch (Exception $e) {
        return $default;
      }
    }

    if (method_exists($options, $name)) {
      try {
        ob_start();
        $result = $options->$name();
        $output = ob_get_clean();
        if ($output !== '') {
          return $output;
        }
        return $result === null ? $default : $result;
      } catch (Exception $e) {
        if (ob_get_level() > 0) {
          ob_end_clean();
        }
        return $default;
      }
    }
  }

  return $default;
}

function printerPaperGetOptions($fallback = null) {
  if (class_exists('Helper') && method_exists('Helper', 'options')) {
    try {
      $options = Helper::options();
      if ($options) {
        return $options;
      }
    } catch (Exception $e) {
      // Fall back to the current archive options below.
    }
  }

  return $fallback;
}

function printerPaperSanitizeWebUrl($url, $siteUrl = '', $allowRelative = true) {
  $url = trim((string) $url);
  if ($url === '') {
    return '';
  }

  if (preg_match('/[\x00-\x1F\x7F<>"\']/u', $url)) {
    return '';
  }

  if (preg_match('/^https?:\/\//i', $url)) {
    return $url;
  }

  if (strpos($url, '//') === 0) {
    $scheme = preg_match('/^https:\/\//i', (string) $siteUrl) ? 'https:' : 'http:';
    return $scheme . $url;
  }

  if (!$allowRelative) {
    return '';
  }

  if ($url[0] === '/') {
    return printerPaperAbsoluteUrl($url, $siteUrl);
  }

  if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
    return '';
  }

  return printerPaperAbsoluteUrl($url, $siteUrl);
}

function printerPaperSanitizeLinkUrl($url, $siteUrl = '', $allowRelative = true) {
  $url = trim((string) $url);
  if ($url === '') {
    return '';
  }

  if (preg_match('/[\x00-\x1F\x7F<>"\']/u', $url)) {
    return '';
  }

  if (preg_match('/^https?:\/\//i', $url)) {
    return $url;
  }

  if (strpos($url, '//') === 0) {
    $scheme = preg_match('/^https:\/\//i', (string) $siteUrl) ? 'https:' : 'http:';
    return $scheme . $url;
  }

  if (!$allowRelative) {
    return '';
  }

  if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
    return '';
  }

  return $url;
}

function printerPaperCssUrlValue($url, $siteUrl = '') {
  $url = printerPaperSanitizeWebUrl($url, $siteUrl, true);
  if ($url === '') {
    return '';
  }

  return 'url("' . addcslashes($url, "\\\"\n\r\f") . '")';
}

function printerPaperPlainText($value, $limit = 0) {
  $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
  $text = preg_replace('/\s+/u', ' ', $text);
  $text = trim($text);

  if ($limit > 0) {
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
      if (mb_strlen($text, 'UTF-8') > $limit) {
        return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '...';
      }
    } elseif (strlen($text) > $limit) {
      return rtrim(substr($text, 0, $limit)) . '...';
    }
  }

  return $text;
}

function printerPaperMarkdownText($value, $limit = 0) {
  $text = printerPaperPlainText($value, $limit);
  $text = str_replace(array("\r", "\n"), ' ', $text);
  return trim($text);
}

function printerPaperMarkdownMultiline($value) {
  $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
  $text = str_replace("\r\n", "\n", $text);
  $text = str_replace("\r", "\n", $text);
  $lines = array();
  foreach (explode("\n", $text) as $line) {
    $line = trim(preg_replace('/\s+/u', ' ', $line));
    if ($line !== '') {
      $lines[] = $line;
    }
  }
  return $lines;
}

function printerPaperMarkdownLink($label, $url) {
  $label = str_replace(array('[', ']'), array('\\[', '\\]'), printerPaperMarkdownText($label));
  $url = trim((string) $url);
  if ($label === '') {
    $label = $url;
  }
  return '[' . $label . '](' . str_replace(')', '%29', $url) . ')';
}

function printerPaperAbsoluteUrl($url, $siteUrl = '') {
  $url = trim((string) $url);
  if ($url === '') {
    return '';
  }

  if (preg_match('/^https?:\/\//i', $url)) {
    return $url;
  }

  $siteUrl = trim((string) $siteUrl);
  if ($siteUrl === '') {
    return '';
  }

  if (strpos($url, '//') === 0) {
    $scheme = preg_match('/^https:\/\//i', $siteUrl) ? 'https:' : 'http:';
    return $scheme . $url;
  }

  if ($url[0] === '/') {
    return rtrim($siteUrl, '/') . $url;
  }

  return rtrim($siteUrl, '/') . '/' . ltrim($url, '/');
}


function printerPaperCurrentUrl($archive, $siteUrl = '') {
  if ($archive && isset($archive->permalink) && trim((string) $archive->permalink) !== '') {
    return (string) $archive->permalink;
  }

  if ($archive && method_exists($archive, 'permalink')) {
    ob_start();
    $archive->permalink();
    $url = trim(ob_get_clean());
    if ($url !== '') {
      return $url;
    }
  }

  return $siteUrl;
}


function printerPaperNormalizeFontFamily($input) {
  $value = trim((string) $input);
  if ($value === '') {
    return '';
  }

  if (strpos($value, ',') !== false) {
    return $value;
  }

  if (strpos($value, '"') !== false || strpos($value, "'") !== false) {
    return $value;
  }

  if (preg_match('/\s/u', $value)) {
    return '"' . $value . '"';
  }

  return $value;
}

function printerPaperNormalizeHexColor($input) {
  $value = trim((string) $input);
  if ($value === '') {
    return '';
  }

  if ($value[0] !== '#') {
    $value = '#' . $value;
  }

  if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value, $m)) {
    return '';
  }

  $hex = strtolower($m[1]);
  if (strlen($hex) === 3) {
    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
  }

  return '#' . $hex;
}

function printerPaperHexToRgbTriplet($hexColor) {
  $hex = printerPaperNormalizeHexColor($hexColor);
  if ($hex === '') {
    return '';
  }

  $hex = substr($hex, 1);
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));

  return $r . ' ' . $g . ' ' . $b;
}

function printerPaperGetRandomCids($count = 4, $categorySlugsText = '') {
  $count = (int) $count;
  if ($count < 1) {
    $count = 4;
  }

  $db = Typecho_Db::get();
  $prefix = $db->getPrefix();

  $select = $db->select('c.cid')
    ->from($prefix . 'contents AS c')
    ->where('c.type = ?', 'post')
    ->where('c.status = ?', 'publish')
    ->where('c.created <= ?', time())
    ->order('c.created', Typecho_Db::SORT_DESC)
    ->limit(200);

  $slugText = trim((string) $categorySlugsText);
  if ($slugText !== '') {
    $slugs = array_values(array_filter(array_map(function ($s) {
      $s = trim($s);
      // 只允许合法的 slug 字符（字母、数字、连字符、下划线）
      return ($s !== '' && preg_match('/^[a-zA-Z0-9_\-]+$/', $s)) ? $s : null;
    }, explode(',', $slugText))));

    if (!empty($slugs)) {
      $midSelect = $db->select('m.mid')
        ->from($prefix . 'metas AS m')
        ->where('m.type = ?', 'category')
        ->where('m.slug IN ?', $slugs);

      $midRows = $db->fetchAll($midSelect);
      $mids = array_map(function ($row) {
        return (int) $row['mid'];
      }, $midRows);

      if (empty($mids)) {
        return array();
      }

      $select->join($prefix . 'relationships AS r', 'c.cid = r.cid', Typecho_Db::LEFT_JOIN)
        ->where('r.mid IN ?', $mids)
        ->group('c.cid');
    }
  }

  $rows = $db->fetchAll($select);
  if (empty($rows)) {
    return array();
  }

  $cids = array_map(function ($row) {
    return (int) $row['cid'];
  }, $rows);

  shuffle($cids);
  return array_slice($cids, 0, $count);
}
