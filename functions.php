<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

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

function printerPaperIsVisitStatsEnabled($options = null) {
  $options = printerPaperGetOptions($options);
  return (string) printerPaperOptionValue($options, 'enableVisitStats', '1') !== '0';
}

function printerPaperEnsureVisitStatsSession() {
  if (function_exists('session_status') && session_status() !== PHP_SESSION_NONE) {
    return;
  }

  if (!function_exists('session_status') && session_id() !== '') {
    return;
  }

  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_only_cookies', 1);
  ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
  @session_start();
}

function printerPaperSendVisitStatsNoCacheHeaders() {
  if (headers_sent()) {
    return;
  }

  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Cache-Control: post-check=0, pre-check=0', false);
  header('Pragma: no-cache');
  header('Expires: 0');
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

function printerPaperLlmUrl($url, $siteUrl = '') {
  $url = trim((string) $url);
  if ($url === '') {
    return '';
  }

  return printerPaperAbsoluteUrl($url, $siteUrl);
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

/**
 * 初始化访问统计数据库表
 * 在主题激活时调用，创建统计数据表
 */
function printerPaperInitVisitStatsTable() {
  static $initialized = false;
  static $available = false;

  if ($initialized) {
    return $available;
  }

  $initialized = true;
  if (!printerPaperIsVisitStatsEnabled()) {
    return false;
  }

  $db = Typecho_Db::get();
  $prefix = $db->getPrefix();
  
  // 检查表是否存在
  $tableName = $prefix . 'printer_visit_stats';
  
  try {
    // 尝试查询表，如果不存在会抛出异常
    $db->fetchRow($db->select('COUNT(*) AS count')->from($tableName));
    $available = true;
  } catch (Exception $e) {
    try {
      // 表不存在，创建表
      $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `stat_date` DATE NOT NULL,
        `total_visits` INT(11) NOT NULL DEFAULT 0,
        `today_visits` INT(11) NOT NULL DEFAULT 0,
        `last_updated` INT(11) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `stat_date` (`stat_date`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

      $db->query($sql);

      // 插入初始记录
      $today = date('Y-m-d');
      $db->query($db->insert($tableName)->rows(array(
        'stat_date' => $today,
        'total_visits' => 0,
        'today_visits' => 0,
        'last_updated' => time()
      )));
      $available = true;
    } catch (Exception $createException) {
      $available = false;
    }
  }

  return $available;
}

/**
 * 访问统计功能（数据库版本）
 * 使用数据库存储统计数据，支持总访问量和个人今日访问量统计
 * 支持后台自定义基数，在此基础上继续累加
 */
function printerPaperGetVisitStats() {
  static $cachedStats = null;

  if ($cachedStats !== null) {
    return $cachedStats;
  }

  if (!printerPaperIsVisitStatsEnabled()) {
    $cachedStats = array('total' => 0, 'today' => 0, 'date' => date('Y-m-d'), 'increment' => 0);
    return $cachedStats;
  }

  printerPaperEnsureVisitStatsSession();

  $db = Typecho_Db::get();
  $prefix = $db->getPrefix();
  $tableName = $prefix . 'printer_visit_stats';
  
  // 获取增量配置
  $options = Helper::options();
  $increment = isset($options->visitIncrement) && is_numeric($options->visitIncrement) && $options->visitIncrement > 0 
    ? (int) $options->visitIncrement 
    : 1;
  
  $today = date('Y-m-d');
  
  // 读取今日统计记录
  try {
    $row = $db->fetchRow(
      $db->select('*')->from($tableName)
        ->where('stat_date = ?', $today)
        ->limit(1)
    );
  } catch (Exception $e) {
    if (!printerPaperInitVisitStatsTable()) {
      $cachedStats = array('total' => 0, 'today' => 0, 'date' => $today, 'increment' => 0);
      return $cachedStats;
    }

    try {
      $row = $db->fetchRow(
        $db->select('*')->from($tableName)
          ->where('stat_date = ?', $today)
          ->limit(1)
      );
    } catch (Exception $retryException) {
      $cachedStats = array('total' => 0, 'today' => 0, 'date' => $today, 'increment' => 0);
      return $cachedStats;
    }
  }
  
  // 如果没有今日记录，说明是新的一天，创建新记录
  if (!$row) {
    // 获取昨日的总访问量
    $yesterdayRow = $db->fetchRow(
      $db->select('total_visits')->from($tableName)
        ->order('stat_date', Typecho_Db::SORT_DESC)
        ->limit(1)
    );
    
    $lastTotal = $yesterdayRow ? (int) $yesterdayRow['total_visits'] : 0;
    
    // 插入新记录
    $db->query($db->insert($tableName)->rows(array(
      'stat_date' => $today,
      'total_visits' => $lastTotal,
      'today_visits' => 0,
      'last_updated' => time()
    )));
    
    $row = array(
      'stat_date' => $today,
      'total_visits' => $lastTotal,
      'today_visits' => 0,
      'last_updated' => time()
    );
  }
  
  // 检查用户是否已经在今日访问过
  // 方式 1：检查 Cookie
  $hasVisitedToday = false;
  $debugInfo = array();
  
  if (isset($_COOKIE['printer_visited_today'])) {
    $cookieValue = $_COOKIE['printer_visited_today'];
    $cookieParts = explode(':', $cookieValue);
    $debugInfo['cookie'] = $cookieValue;
    
    // 检查 Cookie 格式和日期是否都是今天
    if (count($cookieParts) === 2 && $cookieParts[0] === $today) {
      $hasVisitedToday = true;
      $debugInfo['cookie_valid'] = true;
      $debugInfo['cookie_date'] = $cookieParts[0];
    } else {
      $debugInfo['cookie_valid'] = false;
      $debugInfo['cookie_date'] = count($cookieParts) >= 1 ? $cookieParts[0] : 'invalid';
      $debugInfo['today'] = $today;
    }
  } else {
    $debugInfo['cookie'] = 'not_set';
  }
  
  // 方式 2：检查 Session（解决 Cookie 延迟生效问题）
  if (!$hasVisitedToday) {
    $debugInfo['session_status'] = session_status();
    $debugInfo['session_id'] = session_id();
    
    if (isset($_SESSION['printer_visited_today'])) {
      $debugInfo['session_value'] = $_SESSION['printer_visited_today'];
      if ($_SESSION['printer_visited_today'] === $today) {
        $hasVisitedToday = true;
        $debugInfo['session_valid'] = true;
      } else {
        $debugInfo['session_valid'] = false;
        $debugInfo['session_date_mismatch'] = true;
      }
    } else {
      $debugInfo['session_value'] = 'not_set';
    }
  }
  
  // 如果用户今日未访问，增加计数
  if (!$hasVisitedToday) {
    // 更新数据库
    $db->query(
      $db->update($tableName)
        ->rows(array(
          'total_visits' => (int) $row['total_visits'] + $increment,
          'today_visits' => (int) $row['today_visits'] + $increment,
          'last_updated' => time()
        ))
        ->where('stat_date = ?', $today)
    );
    
    // 设置 Cookie（有效期到当天结束，路径设为根目录确保全站可用）
    $expireTime = strtotime('tomorrow') - time();
    
    // 使用 setcookie 的所有参数确保 Cookie 正确保存
    setcookie(
      'printer_visited_today',           // name
      $today . ':1',                     // value
      time() + $expireTime,              // expire
      '/',                               // path (根目录，全站有效)
      '',                                // domain (空表示当前域名)
      isset($_SERVER['HTTPS']),          // secure (HTTPS 时启用)
      true                               // httponly (禁止 JS 访问)
    );
    
    // 同时设置 Session（立即生效，解决当前请求的问题）
    $_SESSION['printer_visited_today'] = $today;
    
    // 强制刷新 $_COOKIE 超全局变量（确保当前脚本后续执行能读取到新值）
    $_COOKIE['printer_visited_today'] = $today . ':1';
    
    $debugInfo['action'] = 'counted';
    $debugInfo['new_cookie'] = $today . ':1';
    
    $cachedStats = array(
      'total' => (int) $row['total_visits'] + $increment,
      'today' => (int) $row['today_visits'] + $increment,
      'date' => $today,
      'increment' => $increment
    );
    return $cachedStats;
  }
  
  $debugInfo['action'] = 'skipped';
  
  $cachedStats = array(
    'total' => (int) $row['total_visits'],
    'today' => (int) $row['today_visits'],
    'date' => $today,
    'increment' => $increment
  );
  return $cachedStats;
}

/**
 * 获取后台自定义的访问数据（如果设置了）
 * 返回 null 表示未设置，使用自动统计
 */
function printerPaperGetCustomVisits($type = 'total') {
  $options = Helper::options();
  if ($type === 'total') {
    return isset($options->siteVisits) && $options->siteVisits !== '' ? (int) $options->siteVisits : null;
  } elseif ($type === 'today') {
    return isset($options->todayVisits) && $options->todayVisits !== '' ? (int) $options->todayVisits : null;
  }
  return null;
}

/**
 * 格式化访问数字，使用 K/M 等单位
 * 例如：1200 → 1.2K, 1500000 → 1.5M
 */
function printerPaperFormatVisitCount($number) {
  $number = (int) $number;
  
  if ($number >= 1000000) {
    // 百万以上
    return round($number / 1000000, 1) . 'M';
  } elseif ($number >= 1000) {
    // 千以上
    return round($number / 1000, 1) . 'K';
  } else {
    // 千以下直接显示
    return number_format($number);
  }
}

/**
 * 获取最终访问数据（数据库版本）
 * 逻辑：总访问 = 自定义基数 + 自动增量；今日访问 = 自定义基数 + 今日增量
 * 使用数据库选项存储自定义基数，避免文件操作
 */
function printerPaperGetFinalVisitStats() {
  static $cachedFinalStats = null;

  if ($cachedFinalStats !== null) {
    return $cachedFinalStats;
  }

  if (!printerPaperIsVisitStatsEnabled()) {
    $cachedFinalStats = array('total' => 0, 'today' => 0);
    return $cachedFinalStats;
  }

  $customTotal = printerPaperGetCustomVisits('total');
  $customToday = printerPaperGetCustomVisits('today');
  
  // 获取自动统计数据
  $autoStats = printerPaperGetVisitStats();
  
  // 确保自动统计数据有效
  if (!is_array($autoStats) || !isset($autoStats['total']) || !isset($autoStats['today'])) {
    $autoStats = array('total' => 0, 'today' => 0, 'date' => date('Y-m-d'));
  }
  
  // 如果设置了自定义基数，作为基数加上自动统计的增量
  if ($customTotal !== null || $customToday !== null) {
    $db = Typecho_Db::get();
    $options = Helper::options();
    $today = date('Y-m-d');
    
    // 从数据库中读取上次记录的基数
    $lastBaseTotal = (int) $options->printerLastBaseTotal;
    $lastBaseToday = (int) $options->printerLastBaseToday;
    $lastBaseDate = $options->printerLastBaseDate;
    
    // 如果是新的一天，重置今日基数
    if ($lastBaseDate !== $today) {
      $lastBaseToday = 0;
      // 更新数据库中的日期
      $options->printerLastBaseDate = $today;
    }
    
    // 计算增量
    $autoTotalIncrement = max(0, $autoStats['total'] - $lastBaseTotal);
    $autoTodayIncrement = max(0, $autoStats['today'] - $lastBaseToday);
    
    // 最终值 = 自定义基数 + 增量
    $finalTotal = ($customTotal !== null ? $customTotal : 0) + $autoTotalIncrement;
    $finalToday = ($customToday !== null ? $customToday : 0) + $autoTodayIncrement;
    
    // 只有当有实际访问增量时才更新基数记录
    if ($autoTotalIncrement > 0 || $autoTodayIncrement > 0) {
      $options->printerLastBaseTotal = $autoStats['total'];
      $options->printerLastBaseToday = $autoStats['today'];
      $options->printerLastBaseDate = $today;
    }
    
    $cachedFinalStats = array(
      'total' => $finalTotal,
      'today' => $finalToday
    );
    return $cachedFinalStats;
  }
  
  // 未设置自定义数据，直接返回自动统计
  $cachedFinalStats = $autoStats;
  return $cachedFinalStats;
}

function printerPaperIsLlmTxtRequest($options = null) {
  if (PHP_SAPI === 'cli' || empty($_SERVER['REQUEST_URI'])) {
    return false;
  }

  $path = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
  if (!is_string($path) || $path === '') {
    return false;
  }

  $path = rawurldecode($path);
  $sitePath = parse_url((string) printerPaperOptionValue($options, 'siteUrl'), PHP_URL_PATH);
  if (is_string($sitePath)) {
    $sitePath = rtrim($sitePath, '/');
    if ($sitePath !== '' && $sitePath !== '/' && strpos($path, $sitePath . '/') === 0) {
      $path = substr($path, strlen($sitePath));
    }
  }

  $path = '/' . ltrim($path, '/');
  $path = rtrim($path, '/');

  return $path === '/llms.txt' || $path === '/index.php/llms.txt';
}

function printerPaperHandleLlmTxtRequest() {
  static $handled = false;
  if ($handled) {
    return;
  }

  $options = printerPaperGetOptions(null);
  if (!printerPaperIsLlmTxtRequest($options)) {
    return;
  }

  $handled = true;
  if (!headers_sent()) {
    http_response_code(200);
    header('X-Robots-Tag: all');
  }

  printerPaperRenderLlmIndex(null);
  exit;
}

printerPaperHandleLlmTxtRequest();
