<?php
/**
 * AI 可读内容出口
 *
 * @template AI 可读内容出口
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

if (function_exists('printerPaperRenderLlmIndex')) {
  printerPaperRenderLlmIndex($this);
  return;
}

header('Content-Type: text/plain; charset=UTF-8');
echo "# LLM index unavailable\n\nThe current theme version does not provide an LLM index renderer.\n";
