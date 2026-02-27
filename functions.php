<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function themeConfig($form) {
  $logoUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'logoUrl',
    NULL,
    '',
    _t('顶部 Logo 图片 URL'),
    _t('可选。填写后会替换左上角圆形图标，例如：https://example.com/logo.png')
  );
  $form->addInput($logoUrl);

  $logoText = new Typecho_Widget_Helper_Form_Element_Text(
    'logoText',
    NULL,
    'Zhinan',
    _t('顶部 Logo 文本'),
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
    _t('favicon.ico 图标路径'),
    _t('可选。支持完整 URL 或站内路径，例如：https://example.com/favicon.ico')
  );
  $form->addInput($faviconUrl);

  $footerCopyright = new Typecho_Widget_Helper_Form_Element_Text(
    'footerCopyright',
    NULL,
    '',
    _t('网页底部版权信息'),
    _t('可选。留空则显示默认版权文案')
  );
  $form->addInput($footerCopyright);

  $analyticsCode = new Typecho_Widget_Helper_Form_Element_Textarea(
    'analyticsCode',
    NULL,
    '',
    _t('网站统计代码'),
    _t('可选。粘贴统计脚本代码（如 Google Analytics、百度统计），将输出在页面底部')
  );
  $form->addInput($analyticsCode);

  $randomReadCategories = new Typecho_Widget_Helper_Form_Element_Text(
    'randomReadCategories',
    NULL,
    '',
    _t('随机阅读分类 slug'),
    _t('可选。多个 slug 用英文逗号分隔，例如：life,tech；留空表示全部分类')
  );
  $form->addInput($randomReadCategories);

  $cnFontCssUrl = new Typecho_Widget_Helper_Form_Element_Text(
    'cnFontCssUrl',
    NULL,
    '',
    _t('中文网字计划 CSS（result.css）'),
    _t('可选。粘贴中文网字计划等字体平台提供的 result.css 链接，例如：https://fontsapi.zeoseven.com/309/main/result.css')
  );
  $form->addInput($cnFontCssUrl);

  $cnFontFamily = new Typecho_Widget_Helper_Form_Element_Text(
    'cnFontFamily',
    NULL,
    '',
    _t('Font-family'),
    _t('可选。填字体名称（CSS 里定义的 font-family）。若包含空格请带引号，例如："LXGW WenKai"')
  );
  $form->addInput($cnFontFamily);

  $cnFontScope = new Typecho_Widget_Helper_Form_Element_Select(
    'cnFontScope',
    array(
      'article' => _t('仅文章/页面内容（推荐）'),
      'paper'   => _t('整张纸区域（含列表页）'),
      'all'     => _t('全站（body）')
    ),
    'article',
    _t('中文字体应用范围'),
    _t('可选。选择字体作用范围；不影响你原有的日/夜模式切换')
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
    _t('文章详情页分类颜色'),
    _t('可选。用于文章详情页顶部分类标签的颜色，支持十六进制颜色，如 #ff6b35 或 #f63')
  );
  $form->addInput($postCategoryColor);
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
    $slugs = array_values(array_filter(array_map('trim', explode(',', $slugText))));

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
