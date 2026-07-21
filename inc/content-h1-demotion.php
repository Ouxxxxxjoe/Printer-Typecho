<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 正文 H1 降级子系统
 *
 * 背景：Typecho 内置 HyperDown 解析器会把 Markdown 的 `# 标题` 原样渲染为 <h1>。
 * 主题 SEO 规范要求"每页仅 1 个 H1"（文章标题），正文标题统一从 ## 起步。
 * 该子系统在渲染输出层兜底，把正文中误用的 <h1> 自动降级为 <h2>，
 * 即便作者疏忽也能保证页面 H1 计数干净（Bing 站长平台收录建议 / Google 语义化结构最佳实践）。
 *
 * 降级规则：仅匹配正文中的开闭 <h1> 标签（含可选属性），不改 h2-h6，不动 <code>/<pre> 内容。
 * 不作为写作规范的替代——作者仍应自觉从 ## 起步；本子系统仅作保险。
 */

/**
 * 把 HTML 字符串中的 <h1>...</h1> 降级为 <h2>...</h2>。
 *
 * @param string $html 已渲染的正文 HTML
 * @return string 降级后的 HTML
 */
function printerPaperDemoteContentH1($html) {
  $html = (string) $html;
  if ($html === '') {
    return $html;
  }

  // 匹配 <h1> 或 <h1 ...>（含可选属性，大小写不敏感），以及对应闭合 </h1>。
  // HyperDown 渲染出的代码块已是 &lt; &gt; 转义形态，不会被本规则误伤。
  // 模式说明：
  //   (<h1)(\s[^>]*)?(>)   →  捕获开标签：$1=标签名前缀, $2=可选属性段, $3=收尾 >
  //   ((?:(?!</?h1\b).)*)  →  标签内文本（非贪婪到首个 </h1>），用否定顺序环视避免跨标签
  //   (</h1>)              →  闭合标签
  $pattern = '/(<h1)(\s[^>]*)?(>)((?:(?!<\/?h1\b).)*)(<\/h1>)/isu';

  return preg_replace_callback($pattern, function ($m) {
    $attrs = isset($m[2]) ? $m[2] : '';
    $inner = isset($m[4]) ? $m[4] : '';
    return '<h2' . $attrs . '>' . $inner . '</h2>';
  }, $html);
}

/**
 * 捕获并降级当前文章正文输出。
 *
 * 用法（在模板里替代 <?php $this->content(); ?>）：
 *   <?php printerPaperRenderContent($this); ?>
 *
 * 实现走 ob 捕获 Typecho 的 content() 输出，经 printerPaperDemoteContentH1 处理后 echo。
 * 与 schema.php / llm.php 的 ob 用法保持一致。
 *
 * @param mixed $archive 当前 Typecho Archive widget（$this）
 */
function printerPaperRenderContent($archive) {
  if (!$archive || !method_exists($archive, 'content')) {
    return;
  }

  ob_start();
  $archive->content();
  $html = ob_get_clean();

  if ($html === false) {
    return;
  }

  echo printerPaperDemoteContentH1($html);
}
