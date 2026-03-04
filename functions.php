<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 启动 Session（用于访问统计去重）
// 注意：必须在任何输出之前启动
if (session_status() === PHP_SESSION_NONE) {
  // 设置 Session 参数（可选，根据需要调整）
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_only_cookies', 1);
  ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
  
  @session_start();
}

// 设置不缓存的 HTTP 头（防止 CDN 缓存包含动态数据的页面）
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

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

// 初始化访问统计数据库表（每次加载时检查，确保表存在）
printerPaperInitVisitStatsTable();

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
    _t('Logo 图片 URL'),
    _t('可选。填写后会替换左上角圆形图标，例如：https://example.com/logo.png')
  );
  $form->addInput($logoUrl);

  $logoText = new Typecho_Widget_Helper_Form_Element_Text(
    'logoText',
    NULL,
    'Zhinan',
    _t('Logo 文本'),
    _t('为空时默认显示站点标题')
  );
  $form->addInput($logoText);

  $subTitle = new Typecho_Widget_Helper_Form_Element_Text(
    'subTitle',
    NULL,
    '',
    _t('网站描述'),
    _t('为空时默认显示站点副标题')
  );
  $form->addInput($subTitle);

  $faviconUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'faviconUrl',
    NULL,
    '',
    _t('网站图标（Favicon）'),
    _t('可选。支持完整 URL 或站内路径，例如：https://example.com/favicon.ico')
  );
  $form->addInput($faviconUrl);

  $footerCopyright = new Typecho_Widget_Helper_Form_Element_Text(
    'footerCopyright',
    NULL,
    '',
    _t('底部版权信息'),
    _t('可选。留空则显示默认版权文案')
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
    _t('随机阅读分类'),
    _t('可选。填写分类别名（英文逗号分隔），例如：life,tech；留空表示全部分类')
  );
  $form->addInput($randomReadCategories);

  $showReadingTime = new Typecho_Widget_Helper_Form_Element_Select(
    'showReadingTime',
    array('1' => _t('显示'), '0' => _t('隐藏')),
    '1',
    _t('文章估读时长'),
    _t('是否在文章详情页显示预计阅读时间（按每分钟阅读 400 字计算）')
  );
  $form->addInput($showReadingTime);

  // ===== 外观样式 =====
  $styleTitle = new Typecho_Widget_Helper_Layout();
  $styleTitle->html('<div class="config-group-title">外观样式</div>');
  $form->addItem($styleTitle);

  $cnFontCssUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'cnFontCssUrl',
    NULL,
    '',
    _t('中文字体 CSS 地址'),
    _t('可选。粘贴中文网字计划等字体平台提供的 result.css 链接，例如：https://fontsapi.zeoseven.com/309/main/result.css')
  );
  $form->addInput($cnFontCssUrl);

  $cnFontFamily = new Typecho_Widget_Helper_Form_Element_Text(
    'cnFontFamily',
    NULL,
    '',
    _t('中文字体名称'),
    _t('可选。填写 CSS 中定义的字体名称。若名称包含空格请用引号，例如："LXGW WenKai"')
  );
  $form->addInput($cnFontFamily);

  $cnFontScope = new Typecho_Widget_Helper_Form_Element_Select(
    'cnFontScope',
    array(
      'article' => _t('仅文章/页面内容（推荐）'),
      'paper'   => _t('整张纸区域（含列表页）'),
      'all'     => _t('全站')
    ),
    'article',
    _t('中文字体应用范围'),
    _t('可选。选择字体作用范围')
  );
  $form->addInput($cnFontScope);

  $externalLinkColor = new Typecho_Widget_Helper_Form_Element_Text(
    'externalLinkColor',
    NULL,
    '#ff6b35',
    _t('外链颜色'),
    _t('可选。支持十六进制颜色，如 #ff6b35 或 #f63')
  );
  $form->addInput($externalLinkColor);

  $postCategoryColor = new Typecho_Widget_Helper_Form_Element_Text(
    'postCategoryColor',
    NULL,
    '#ff6b35',
    _t('分类标签颜色'),
    _t('可选。用于文章详情页顶部分类标签的颜色，支持十六进制颜色，如 #ff6b35 或 #f63')
  );
  $form->addInput($postCategoryColor);

  // ===== 统计与追踪 =====
  $analyticsTitle = new Typecho_Widget_Helper_Layout();
  $analyticsTitle->html('<div class="config-group-title">统计与追踪</div>');
  $form->addItem($analyticsTitle);

  $analyticsCode = new Typecho_Widget_Helper_Form_Element_Textarea(
    'analyticsCode',
    NULL,
    '',
    _t('统计代码'),
    _t('可选。粘贴统计脚本代码（如 Google Analytics、百度统计），将输出在页面底部')
  );
  $form->addInput($analyticsCode);

  $siteVisits = new Typecho_Widget_Helper_Form_Element_Text(
    'siteVisits',
    NULL,
    '',
    _t('总访问量'),
    _t('可选。设个初始值，系统会自动累加新访问。留空则从 0 开始')
  );
  $form->addInput($siteVisits);

  $todayVisits = new Typecho_Widget_Helper_Form_Element_Text(
    'todayVisits',
    NULL,
    '',
    _t('今日访问基数'),
    _t('可选。给今日访问设个初始值，系统会自动累加新访问。留空则从 0 开始')
  );
  $form->addInput($todayVisits);

  $visitIncrement = new Typecho_Widget_Helper_Form_Element_Text(
    'visitIncrement',
    NULL,
    '1',
    _t('每次访问增量'),
    _t('默认为 1。设为 2 就是每次访问 +2，设为 3 就是 +3，以此类推')
  );
  $form->addInput($visitIncrement);
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
  $db = Typecho_Db::get();
  $prefix = $db->getPrefix();
  
  // 检查表是否存在
  $tableName = $prefix . 'printer_visit_stats';
  
  try {
    // 尝试查询表，如果不存在会抛出异常
    $db->fetchRow($db->select('COUNT(*) AS count')->from($tableName));
  } catch (Exception $e) {
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
  }
}

/**
 * 访问统计功能（数据库版本）
 * 使用数据库存储统计数据，支持总访问量和个人今日访问量统计
 * 支持后台自定义基数，在此基础上继续累加
 */
function printerPaperGetVisitStats() {
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
  $row = $db->fetchRow(
    $db->select('*')->from($tableName)
      ->where('stat_date = ?', $today)
      ->limit(1)
  );
  
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
    
    return array(
      'total' => (int) $row['total_visits'] + $increment,
      'today' => (int) $row['today_visits'] + $increment,
      'date' => $today,
      'increment' => $increment
    );
  }
  
  $debugInfo['action'] = 'skipped';
  
  return array(
    'total' => (int) $row['total_visits'],
    'today' => (int) $row['today_visits'],
    'date' => $today,
    'increment' => $increment
  );
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
    
    return array(
      'total' => $finalTotal,
      'today' => $finalToday
    );
  }
  
  // 未设置自定义数据，直接返回自动统计
  return $autoStats;
}
