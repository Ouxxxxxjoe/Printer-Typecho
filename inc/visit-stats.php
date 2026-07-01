<?php
/**
 * Printer 主题 - 访问统计子系统
 *
 * 由 functions.php 顶部 require_once 加载。
 * 唯一碰数据库的子系统：建表 / 计数 / Cookie+Session 去重 / 自定义基数叠加。
 *
 * @package Printer
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

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
