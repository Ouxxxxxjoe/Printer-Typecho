<?php
namespace TypechoPlugin\Printerllm;

use Typecho\Widget;
use Widget\ActionInterface;
use Widget\Options;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * /action/Printerllm 入口
 *
 * Typecho 的 /action/<name> 路由由 Widget\Action 派发：它会在 actionTable 里查 name，
 * 实例化对应 widget 类（必须继承 Typecho\Widget 并实现 ActionInterface），再调用其 action()。
 * 插件主类 Plugin 是静态实现、且不继承 Widget，不能直接当 action 处理器，故独立本类。
 *
 * 仅管理员可调用，触发手动重新生成，随后跳回插件设置页。
 *
 * @package Printerllm
 * @author  zhinan
 * @link    https://zhinan.blog/
 */
class Action extends Widget implements ActionInterface
{
    /**
     * 入口：鉴权 → 重新生成 → 跳回设置页。
     */
    public function action()
    {
        // 鉴权：必须是已登录的管理员
        try {
            $user = Widget::widget('Widget\User');
            if (!$user->hasLogin() || !$user->pass('administrator', true)) {
                http_response_code(403);
                echo 'Forbidden';
                return;
            }
        } catch (\Throwable $e) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        try {
            Plugin::regenerate('manual');
        } catch (\Throwable $e) {
            // 失败已写入 .status.json，继续跳转让用户在设置页看到错误
        }

        $options = Options::alloc();
        $url = rtrim((string) $options->adminUrl, '/')
             . '/options-plugin.php?config=Printerllm#llmstxt-status';
        @header('Location: ' . $url);
        exit;
    }
}
