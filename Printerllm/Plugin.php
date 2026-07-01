<?php
namespace TypechoPlugin\Printerllm;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Db;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Select;
use Widget\Options;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * Printerllm（Printer主题）配套的 llms.txt 生成插件。
 *
 * 在站点根目录输出符合 llmstxt.org 规范的 llms.txt（索引）与 llms-full.txt（全文版）。
 *
 * @package Printerllm
 * @author  zhinan
 * @version 1.0.0
 * @link    https://zhinan.blog/
 */
class Plugin implements PluginInterface
{
    /** 插件状态文件名（写入插件自身目录） */
    const STATUS_FILE = '.status.json';

    /**
     * 启用：挂载发布钩子并立即生成一次。
     */
    public static function activate()
    {
        // 注意：本类名为 Plugin，因此不能 `use Typecho\Plugin;` 后写 `Plugin::factory()`——
        // PHP 编译期会对"use 导入别名 Plugin"与"声明的 class Plugin"判定重名，
        // 报 "Cannot declare class ... because the name is already in use"。故用全限定名。
        // 覆盖文章 / 页面内容变更的所有路径，任何一条触发都立即重新生成：
        //   finishPublish：写新文章点「发布」、编辑已发布文章后点「发布」
        //   finishMark   ：文章列表里「标记为 发布/隐藏/草稿/私密」（含 草稿→发布）
        //   finishDelete ：删除文章 / 页面
        // （finishSave 只在「已发布→存草稿」这种边缘场景相关，草稿本就不进 llms.txt，故不挂，避免每次存草稿白跑一遍）
        foreach (array('Widget\Contents\Post\Edit', 'Widget\Contents\Page\Edit') as $handle) {
            \Typecho\Plugin::factory($handle)->finishPublish = __CLASS__ . '::onPublish';
            \Typecho\Plugin::factory($handle)->finishMark    = __CLASS__ . '::onMark';
            \Typecho\Plugin::factory($handle)->finishDelete  = __CLASS__ . '::onDelete';
        }
        // 兼容旧版（1.1）下划线类名；1.3 上 nativeClassName 后与上面同 handle，冗余但无害
        foreach (array('Widget_Contents_Post_Edit', 'Widget_Contents_Page_Edit') as $handle) {
            \Typecho\Plugin::factory($handle)->finishPublish = __CLASS__ . '::onPublish';
            \Typecho\Plugin::factory($handle)->finishMark    = __CLASS__ . '::onMark';
            \Typecho\Plugin::factory($handle)->finishDelete  = __CLASS__ . '::onDelete';
        }

        // 注册 /action/Printerllm 入口，指向本插件的 Action widget（见 Action.php）。
        // Typecho 不会自动把插件映射到 /action/<插件名>，必须显式注册到 actionTable。
        \Utils\Helper::addAction('Printerllm', __NAMESPACE__ . '\\Action');

        try {
            self::regenerate('activate');
        } catch (\Throwable $e) {
            // 启用阶段失败不阻塞，用户可在设置页重试
        }

        return _t('Printerllm 已启用。发布、编辑、改状态、删除文章 / 页面时将自动更新根目录的 llms.txt 与 llms-full.txt。');
    }

    /**
     * 停用：注销 action 入口；保留已生成的静态文件（如需删除请手动移除根目录下的 llms.txt / llms-full.txt）。
     */
    public static function deactivate()
    {
        try {
            \Utils\Helper::removeAction('Printerllm');
        } catch (\Throwable $e) {
            // actionTable 已不存在等异常忽略，不影响停用
        }
        return _t('Printerllm 已停用。根目录的 llms.txt / llms-full.txt 未被删除，如不再需要请手动移除。');
    }

    /**
     * 插件设置表单。
     */
    public static function config(Form $form)
    {
        $enabled = new Select(
            'enabled',
            array('1' => _t('启用'), '0' => _t('关闭（仅保留已生成文件，不再更新）')),
            '1',
            _t('是否生成')
        );
        $form->addInput($enabled);

        $indexPostLimit = new Text(
            'indexPostLimit',
            NULL,
            '20',
            _t('llms.txt 文章数'),
            _t('llms.txt 索引中列出的最近文章数量。llms.txt 是主文件（遵循 llmstxt.org 规范），始终生成。默认 20。')
        );
        $form->addInput($indexPostLimit);

        $fullPostLimit = new Text(
            'fullPostLimit',
            NULL,
            '',
            _t('llms-full.txt 收录文章数（可选）'),
            _t('llms-full.txt 拼接最近多少篇文章的全文。留空则不生成 llms-full.txt；填写则建议 30 - 200。')
        );
        $form->addInput($fullPostLimit);

        // 状态信息 + 手动重生成按钮
        $statusHtml = self::statusHtml();
        $layout = new Layout();
        $layout->html('<div class="config-group-title">运行状态</div>' . $statusHtml);
        $form->addItem($layout);
    }

    public static function personalConfig(Form $form)
    {
    }

    /**
     * finishPublish 钩子：发布 / 重新发布文章后更新文件。失败绝不阻塞发布。
     */
    public static function onPublish()
    {
        try {
            self::regenerate('publish');
        } catch (\Throwable $e) {
            // 静默吞掉，保证发布流程不受影响
        }
    }

    /**
     * finishMark 钩子：文章列表里改变状态（发布/隐藏/草稿/私密等）后更新文件。失败绝不阻塞。
     */
    public static function onMark()
    {
        try {
            self::regenerate('mark');
        } catch (\Throwable $e) {
        }
    }

    /**
     * finishDelete 钩子：删除文章 / 页面后更新文件。失败绝不阻塞。
     */
    public static function onDelete()
    {
        try {
            self::regenerate('delete');
        } catch (\Throwable $e) {
        }
    }

    /* =====================================================================
     *  生成核心
     * ===================================================================== */

    /**
     * 编排：构建并写入两个文件。返回状态数组。
     */
    public static function regenerate($trigger = 'manual')
    {
        $result = array(
            'ok'      => false,
            'trigger' => $trigger,
            'time'    => time(),
            'index'   => null,
            'full'    => null,
            'error'   => '',
        );

        $enabled = self::pluginOption('enabled', '1');
        if ($enabled === '0') {
            $result['error'] = '插件已在设置中关闭';
            self::writeStatus($result);
            return $result;
        }

        $root = self::rootDir();
        if ($root === false) {
            $result['error'] = '无法定位 Typecho 根目录（__TYPECHO_ROOT_DIR__ 未定义）';
            self::writeStatus($result);
            return $result;
        }

        try {
            self::ensureThemeFunctions();
            $options = self::themeOptions();

            // llms.txt 是主文件，始终生成（buildIndex 内部对 indexPostLimit 留空有默认回退）
            $index = self::buildIndex($options);
            $r1 = self::writeFile($root . '/llms.txt', $index);

            // llms-full.txt 可选：fullPostLimit 留空 / 0 → 不生成，并清掉残留旧文件
            $fullLimitRaw = (string) self::pluginOption('fullPostLimit', '');
            if ($fullLimitRaw !== '' && (int) $fullLimitRaw > 0) {
                $fullLimit = (int) $fullLimitRaw;
                if ($fullLimit > 5000) {
                    $fullLimit = 5000;
                }
                $full = self::buildFull($options, $fullLimit);
                $r2 = self::writeFile($root . '/llms-full.txt', $full);
            } else {
                $fullPath = $root . '/llms-full.txt';
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
                $r2 = array('ok' => true, 'skipped' => true, 'path' => $fullPath);
            }

            $result['index'] = $r1;
            $result['full']  = $r2;
            $result['ok']    = ($r1['ok'] && $r2['ok']);
            if (!$result['ok']) {
                $errs = array();
                if (!empty($r1['error'])) {
                    $errs[] = 'llms.txt: ' . $r1['error'];
                }
                if (empty($r2['skipped']) && !empty($r2['error'])) {
                    $errs[] = 'llms-full.txt: ' . $r2['error'];
                }
                $result['error'] = implode('；', $errs);
            }
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        self::writeStatus($result);
        return $result;
    }

    /**
     * 构建 llms.txt（遵循 llmstxt.org 规范）。
     * 顺序：H1 标题 → 引用块摘要 → 非标题说明段落 → 若干 H2 小节链接列表（含 ## Optional）。
     */
    private static function buildIndex($options)
    {
        $hasTheme = self::hasTheme();

        // 站点名 / 站点地址
        $siteName = self::mdText(self::opt($options, 'logoText'));
        if ($siteName === '') {
            $siteName = self::mdText(self::opt($options, 'title'));
        }
        if ($siteName === '') {
            $siteName = 'Untitled Site';
        }

        $siteUrl = rtrim((string) self::opt($options, 'siteUrl', rtrim((string) Options::alloc()->siteUrl, '/')), '/') . '/';

        // 摘要（优先主题 AI 摘要，否则站点描述，再否则通用语句）
        $summary = self::mdText(self::opt($options, 'aiSiteSummary'), 500);
        if ($summary === '') {
            $summary = self::mdText(self::opt($options, 'description'), 500);
        }
        if ($summary === '') {
            $summary = $siteName . ' —— 一个使用 Typecho 构建的站点。';
        }

        // 链接资源
        $feedUrl     = self::resolveUrl(self::opt($options, 'llmFeedUrl', '/feed/'), $siteUrl);
        $sitemapUrl  = self::resolveUrl(self::opt($options, 'llmSitemapUrl', '/sitemap.xml'), $siteUrl);
        $llmPageUrl  = '';
        if ($hasTheme && function_exists('printerPaperLlmIndexUrl')) {
            try {
                // 同样过 resolveUrl，保证主题函数若返回相对路径也能转成绝对路径
                $llmPageUrl = self::resolveUrl((string) printerPaperLlmIndexUrl($options), $siteUrl);
            } catch (\Throwable $e) {
                $llmPageUrl = '';
            }
        }

        // 限制（索引文章数：插件设置 → 主题设置 → 默认 20）
        $indexLimit = (int) self::pluginOption('indexPostLimit', '');
        if ($indexLimit < 1) {
            if ($hasTheme && function_exists('printerPaperLlmLimit')) {
                $indexLimit = (int) printerPaperLlmLimit($options);
            } else {
                $indexLimit = 20;
            }
        }
        if ($indexLimit < 1) {
            $indexLimit = 20;
        }
        if ($indexLimit > 500) {
            $indexLimit = 500;
        }

        // 输出模式
        $mode = self::opt($options, 'llmPostOutputMode', 'summary');
        if (!in_array($mode, array('link', 'summary', 'full'), true)) {
            $mode = 'summary';
        }

        $includeCats  = (string) self::opt($options, 'llmIncludeCategories', '1') !== '0';
        $includePages = (string) self::opt($options, 'llmIncludePages', '1') !== '0';

        // 是否生成 llms-full.txt（决定索引里是否提及 / 推荐全文版，避免出现死链）
        $fullLimitRaw = (string) self::pluginOption('fullPostLimit', '');
        $generateFull = ($fullLimitRaw !== '' && (int) $fullLimitRaw > 0);

        // ---------- 组装 ----------
        $out = '';
        $out .= '# ' . $siteName . "\n\n";
        $out .= '> ' . str_replace("\n", "\n> ", $summary) . "\n";
        $out .= '> 本文件遵循 llmstxt.org 规范，最后更新于 ' . date('Y-m-d') . '。' . "\n";
        if ($generateFull) {
            $out .= '> 全文版见同目录 llms-full.txt。' . "\n";
        }
        $out .= "\n";

        // 非标题说明区（列表形式）
        $lines = array();

        $readInstructions = self::mdLines(self::opt($options, 'llmReadInstructions'));
        if (empty($readInstructions)) {
            $readInstructions = array(
                '先读本索引了解站点范围，再按需读取具体文章 URL。',
                'RSS / Sitemap 用于发现，文章 URL 用于引用细节。',
                '引用时请保留文章标题与原始链接。'
            );
        }
        if (!empty($readInstructions)) {
            $lines[] = '';
            $lines[] = '读取建议：';
            foreach ($readInstructions as $l) {
                $lines[] = '- ' . $l;
            }
        }

        $scope = self::mdLines(self::opt($options, 'llmScopeNotes'));
        if (!empty($scope)) {
            $lines[] = '';
            $lines[] = '适合回答的问题：';
            foreach ($scope as $l) {
                $lines[] = '- ' . $l;
            }
        }

        $notServed = self::mdLines(self::opt($options, 'llmNotServedNotes'));
        if (!empty($notServed)) {
            $lines[] = '';
            $lines[] = '不作为内容来源：';
            foreach ($notServed as $l) {
                $lines[] = '- ' . $l;
            }
        }

        $citation = self::mdLines(self::opt($options, 'llmCitationNotes'));
        if (!empty($citation)) {
            $lines[] = '';
            $lines[] = '引用说明：';
            foreach ($citation as $l) {
                $lines[] = '- ' . $l;
            }
        }

        if (!empty($lines)) {
            $out .= implode("\n", $lines) . "\n\n";
        }

        // ## Recommended（主路径）
        $out .= "## Recommended\n\n";
        $out .= '- ' . self::mdLink('Home', $siteUrl) . "\n";
        if ($generateFull) {
            $out .= '- ' . self::mdLink('Full text (llms-full.txt)', $siteUrl . 'llms-full.txt') . ': 最近文章全文拼接，供一次性读取' . "\n";
        }
        $out .= "\n";

        // ## Categories
        if ($includeCats) {
            $out .= "## Categories\n\n";
            $cats = self::llCategories();
            if (empty($cats)) {
                $out .= '- （暂无公开分类）' . "\n";
            } else {
                foreach ($cats as $cat) {
                    $name = self::mdText(isset($cat['name']) ? $cat['name'] : '');
                    $url  = self::llCategoryUrl($cat, $options);
                    $count = isset($cat['count']) ? (int) $cat['count'] : 0;
                    $desc = self::mdText(isset($cat['description']) ? $cat['description'] : '', 140);
                    $out .= '- ' . self::mdLink($name, $url) . ' (' . $count . ' 篇)';
                    if ($desc !== '') {
                        $out .= ': ' . $desc;
                    }
                    $out .= "\n";
                }
            }
            $out .= "\n";
        }

        // ## Recent posts
        $out .= "## Recent posts\n\n";
        $posts = self::llRecentPosts($indexLimit);
        if (empty($posts)) {
            $out .= '- （暂无公开文章）' . "\n";
        } else {
            foreach ($posts as $post) {
                $title = self::mdText(isset($post['title']) ? $post['title'] : '');
                $url   = self::llPostUrl($post, $options);
                $date  = isset($post['created']) ? date('Y-m-d', (int) $post['created']) : '';
                $out .= '- ' . self::mdLink($title, $url);
                if ($date !== '') {
                    $out .= ' — ' . $date;
                }
                if ($mode !== 'link') {
                    $excerpt = self::excerpt(isset($post['text']) ? $post['text'] : '', $mode);
                    if ($excerpt !== '') {
                        $out .= ': ' . $excerpt;
                    }
                }
                $out .= "\n";
            }
        }
        $out .= "\n";

        // ## Pages
        if ($includePages) {
            $out .= "## Pages\n\n";
            $pages = self::llPages();
            if (empty($pages)) {
                $out .= '- （暂无独立页面）' . "\n";
            } else {
                foreach ($pages as $page) {
                    $title = self::mdText(isset($page['title']) ? $page['title'] : '');
                    $url   = self::llPageUrl($page, $options);
                    $desc  = self::mdText(isset($page['text']) ? $page['text'] : '', 160);
                    $out .= '- ' . self::mdLink($title, $url);
                    if ($desc !== '') {
                        $out .= ': ' . $desc;
                    }
                    $out .= "\n";
                }
            }
            $out .= "\n";
        }

        // ## Optional（次要资源，需要更短上下文时可跳过）
        $optItems = array();
        if ($feedUrl !== '') {
            $optItems[] = '- ' . self::mdLink('RSS', $feedUrl);
        }
        if ($sitemapUrl !== '') {
            $optItems[] = '- ' . self::mdLink('Sitemap', $sitemapUrl);
        }
        if ($llmPageUrl !== '') {
            $optItems[] = '- ' . self::mdLink('LLM readable index page', $llmPageUrl);
        }
        if (!empty($optItems)) {
            $out .= "## Optional\n\n";
            $out .= implode("\n", $optItems) . "\n";
        }

        return $out;
    }

    /**
     * 构建 llms-full.txt：拼接最近文章全文。
     */
    private static function buildFull($options, $limit)
    {
        $siteName = self::mdText(self::opt($options, 'logoText'));
        if ($siteName === '') {
            $siteName = self::mdText(self::opt($options, 'title'));
        }
        if ($siteName === '') {
            $siteName = 'Untitled Site';
        }

        $summary = self::mdText(self::opt($options, 'aiSiteSummary'), 500);
        if ($summary === '') {
            $summary = self::mdText(self::opt($options, 'description'), 500);
        }

        $posts = self::llRecentPosts($limit);

        // 批量取分类 cid => [name]
        $catMap = array();
        if (!empty($posts)) {
            $cids = array();
            foreach ($posts as $p) {
                if (!empty($p['cid'])) {
                    $cids[] = (int) $p['cid'];
                }
            }
            $catMap = self::postCategoriesMap($cids);
        }

        $out = '';
        $out .= '# ' . $siteName . " — Full Text\n\n";
        $out .= '> ' . str_replace("\n", "\n> ", $summary) . "\n";
        $out .= '> 本文件由 Printerllm 插件生成，拼接最近 ' . count($posts) . ' 篇文章的全文，最后更新于 ' . date('Y-m-d') . "。\n";
        $out .= "> 索引版见同目录 llms.txt。\n\n";

        if (empty($posts)) {
            $out .= "（暂无公开文章）\n";
            return $out;
        }

        foreach ($posts as $i => $post) {
            $title = self::mdText(isset($post['title']) ? $post['title'] : '');
            $url   = self::llPostUrl($post, $options);
            $date  = isset($post['created']) ? date('Y-m-d', (int) $post['created']) : '';
            $cid   = isset($post['cid']) ? (int) $post['cid'] : 0;
            $cats  = isset($catMap[$cid]) ? $catMap[$cid] : array();

            if ($i > 0) {
                $out .= "\n---\n\n";
            }
            $out .= '## ' . ($title !== '' ? $title : 'Untitled') . "\n\n";
            $meta = array();
            $meta[] = '- 原文: ' . $url;
            if ($date !== '') {
                $meta[] = '- 日期: ' . $date;
            }
            if (!empty($cats)) {
                $meta[] = '- 分类: ' . implode('、', $cats);
            }
            $out .= implode("\n", $meta) . "\n\n";

            $body = self::cleanBody(isset($post['text']) ? $post['text'] : '');
            $out .= trim($body) . "\n\n";
        }

        return $out;
    }

    /* =====================================================================
     *  数据访问层（优先复用主题函数，否则最小化自包含查询）
     * ===================================================================== */

    private static function hasTheme()
    {
        return function_exists('printerPaperLlmRecentPosts');
    }

    private static function llRecentPosts($limit)
    {
        if (self::hasTheme()) {
            return printerPaperLlmRecentPosts($limit);
        }
        // 降级：直接查询
        try {
            $db = Db::get();
            $prefix = $db->getPrefix();
            $limit = max(1, (int) $limit);
            $sql = "SELECT cid, title, slug, created, modified, text FROM `{$prefix}contents`"
                 . " WHERE type = 'post' AND status = 'publish' AND created <= " . (int) time()
                 . " ORDER BY created DESC LIMIT " . $limit;
            return $db->fetchAll($sql);
        } catch (\Throwable $e) {
            return array();
        }
    }

    private static function llPages()
    {
        if (self::hasTheme() && function_exists('printerPaperLlmPages')) {
            return printerPaperLlmPages();
        }
        try {
            $db = Db::get();
            $prefix = $db->getPrefix();
            $sql = "SELECT cid, title, slug, created, modified, text FROM `{$prefix}contents`"
                 . " WHERE type = 'page' AND status = 'publish' AND created <= " . (int) time()
                 . " ORDER BY `order` ASC";
            return $db->fetchAll($sql);
        } catch (\Throwable $e) {
            return array();
        }
    }

    private static function llCategories()
    {
        if (self::hasTheme() && function_exists('printerPaperLlmCategories')) {
            return printerPaperLlmCategories();
        }
        try {
            $db = Db::get();
            $prefix = $db->getPrefix();
            $sql = "SELECT mid, name, slug, count, description FROM `{$prefix}metas`"
                 . " WHERE type = 'category' ORDER BY `order` ASC";
            return $db->fetchAll($sql);
        } catch (\Throwable $e) {
            return array();
        }
    }

    private static function llPostUrl($row, $options)
    {
        if (self::hasTheme() && function_exists('printerPaperLlmPostUrl')) {
            try {
                return printerPaperLlmPostUrl($row, $options);
            } catch (\Throwable $e) {
                // 落到兜底
            }
        }
        $siteUrl = rtrim((string) self::opt($options, 'siteUrl', Options::alloc()->siteUrl), '/');
        $cid = isset($row['cid']) ? (int) $row['cid'] : 0;
        return $siteUrl . '/?p=' . $cid;
    }

    private static function llPageUrl($row, $options)
    {
        if (self::hasTheme() && function_exists('printerPaperLlmPageUrl')) {
            try {
                return printerPaperLlmPageUrl($row, $options);
            } catch (\Throwable $e) {
            }
        }
        $siteUrl = rtrim((string) self::opt($options, 'siteUrl', Options::alloc()->siteUrl), '/');
        $cid = isset($row['cid']) ? (int) $row['cid'] : 0;
        return $siteUrl . '/?page_id=' . $cid;
    }

    private static function llCategoryUrl($row, $options)
    {
        if (self::hasTheme() && function_exists('printerPaperLlmCategoryUrl')) {
            try {
                return printerPaperLlmCategoryUrl($row, $options);
            } catch (\Throwable $e) {
            }
        }
        $siteUrl = rtrim((string) self::opt($options, 'siteUrl', Options::alloc()->siteUrl), '/');
        $slug = isset($row['slug']) ? $row['slug'] : '';
        if ($slug === '') {
            return $siteUrl . '/';
        }
        return $siteUrl . '/?category=' . rawurlencode($slug);
    }

    /**
     * 批量取文章分类：cid => [name, ...]
     */
    private static function postCategoriesMap($cids)
    {
        $cids = array_values(array_filter(array_map('intval', $cids)));
        if (empty($cids)) {
            return array();
        }
        try {
            $db = Db::get();
            $prefix = $db->getPrefix();
            $cidList = implode(',', $cids);
            $sql = "SELECT r.cid, m.name FROM `{$prefix}relationships` r"
                 . " LEFT JOIN `{$prefix}metas` m ON r.mid = m.mid"
                 . " WHERE r.cid IN ({$cidList}) AND m.type = 'category'"
                 . " ORDER BY r.cid ASC, m.`order` ASC";
            $rows = $db->fetchAll($sql);
            $map = array();
            foreach ($rows as $r) {
                $cid = (int) $r['cid'];
                if (!isset($map[$cid])) {
                    $map[$cid] = array();
                }
                $map[$cid][] = self::mdText($r['name']);
            }
            return $map;
        } catch (\Throwable $e) {
            return array();
        }
    }

    /* =====================================================================
     *  文本 / URL 工具（优先主题实现）
     * ===================================================================== */

    private static function opt($options, $name, $default = '')
    {
        if (function_exists('printerPaperOptionValue')) {
            return printerPaperOptionValue($options, $name, $default);
        }
        // 最小兜底
        if (!$options) {
            return $default;
        }
        try {
            if (is_object($options) && isset($options->$name)) {
                return $options->$name === null ? $default : $options->$name;
            }
        } catch (\Throwable $e) {
        }
        return $default;
    }

    private static function mdText($value, $limit = 0)
    {
        if (function_exists('printerPaperMarkdownText')) {
            return printerPaperMarkdownText($value, $limit);
        }
        $t = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8')));
        if ($limit > 0 && function_exists('mb_strlen') && mb_strlen($t, 'UTF-8') > $limit) {
            $t = rtrim(mb_substr($t, 0, $limit, 'UTF-8')) . '...';
        }
        return $t;
    }

    private static function mdLines($value)
    {
        if (function_exists('printerPaperMarkdownMultiline')) {
            return printerPaperMarkdownMultiline($value);
        }
        $t = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        $t = str_replace(array("\r\n", "\r"), "\n", $t);
        $out = array();
        foreach (explode("\n", $t) as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', $line));
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return $out;
    }

    private static function mdLink($label, $url)
    {
        if (function_exists('printerPaperMarkdownLink')) {
            return printerPaperMarkdownLink($label, $url);
        }
        $label = str_replace(array('[', ']'), array('\\[', '\\]'), self::mdText($label));
        $url = trim((string) $url);
        if ($label === '') {
            $label = $url;
        }
        return '[' . $label . '](' . str_replace(')', '%29', $url) . ')';
    }

    private static function excerpt($text, $mode)
    {
        if (function_exists('printerPaperLlmExcerpt')) {
            return printerPaperLlmExcerpt($text, $mode);
        }
        $limit = $mode === 'full' ? 600 : 180;
        return self::mdText($text, $limit);
    }

    private static function resolveUrl($url, $siteUrl)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        // 相对地址：拼到站点根
        return rtrim($siteUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * 清洗文章正文：去掉 Typecho 的 <!--more--> 与首部可能的标记注释。
     */
    private static function cleanBody($text)
    {
        $text = (string) $text;
        // 去掉 more 分隔
        $text = preg_replace('/<!--\s*more\s*-->/i', '', $text);
        // 去掉开头的标记注释
        $text = preg_replace('/^\s*<!--[^>]*-->\s*/', '', $text);
        return $text;
    }

    /* =====================================================================
     *  文件写入 / 状态
     * ===================================================================== */

    private static function rootDir()
    {
        if (defined('__TYPECHO_ROOT_DIR__') && is_dir(__TYPECHO_ROOT_DIR__)) {
            return rtrim(str_replace('\\', '/', __TYPECHO_ROOT_DIR__), '/');
        }
        return false;
    }

    /**
     * 原子写：临时文件 + rename。
     */
    private static function writeFile($path, $content)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            return array('ok' => false, 'error' => '目标目录不存在', 'path' => $path, 'writable' => false);
        }

        $tmp = $path . '.tmp.';
        try {
            $tmp .= function_exists('random_bytes') ? bin2hex(random_bytes(4)) : mt_rand(0, 9999999);
        } catch (\Throwable $e) {
            $tmp .= mt_rand(0, 9999999);
        }

        $written = @file_put_contents($tmp, $content);
        if ($written === false) {
            @unlink($tmp);
            return array(
                'ok'        => false,
                'error'     => '写入失败（根目录可能无写权限）',
                'path'      => $path,
                'writable'  => @is_writable($dir),
            );
        }

        if (!@rename($tmp, $path)) {
            // 跨卷等情况下 rename 可能失败：尝试直接覆盖写
            @unlink($tmp);
            $direct = @file_put_contents($path, $content);
            if ($direct === false) {
                return array(
                    'ok'        => false,
                    'error'     => '重命名失败（根目录可能无写权限）',
                    'path'      => $path,
                    'writable'  => @is_writable($dir),
                );
            }
        }

        @chmod($path, 0644);
        return array('ok' => true, 'path' => $path, 'bytes' => strlen($content), 'writable' => true);
    }

    private static function statusFilePath()
    {
        return dirname(__FILE__) . '/' . self::STATUS_FILE;
    }

    private static function writeStatus($result)
    {
        try {
            $path = self::statusFilePath();
            @file_put_contents($path, json_encode($result, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            // 状态写不进去不影响主流程
        }
    }

    private static function readStatus()
    {
        $path = self::statusFilePath();
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * 渲染设置页的运行状态块（含手动重生成按钮）。
     */
    private static function statusHtml()
    {
        $options = Options::alloc();
        // /action/<插件名> 对插件名大小写敏感，须与目录名 Printerllm 完全一致
        $indexUrl = (string) $options->index;
        if ($indexUrl === '') {
            $indexUrl = rtrim((string) $options->siteUrl, '/');
        }
        $actionUrl = rtrim($indexUrl, '/') . '/action/Printerllm';
        $status = self::readStatus();

        $root = self::rootDir();
        $rootOk = ($root !== false && @is_writable($root));

        // 真实文件探测
        $indexPath = ($root !== false) ? $root . '/llms.txt' : '';
        $fullPath  = ($root !== false) ? $root . '/llms-full.txt' : '';
        $indexExists = ($indexPath !== '' && is_file($indexPath));
        $fullExists  = ($fullPath !== '' && is_file($fullPath));

        $html  = '<div id="llmstxt-status" style="background:#fcfbf8;border:1px solid #e4dccd;border-radius:8px;padding:12px 14px;margin:6px 0 14px;font-size:13px;line-height:1.7">';
        $html .= '<div><b>根目录写权限：</b>' . ($rootOk ? '<span style="color:#2e7d32">可写（' . htmlspecialchars($root) . '）</span>' : '<span style="color:#c62828">不可写或未定位</span>') . '</div>';

        $html .= '<div><b>llms.txt：</b>';
        if ($indexExists) {
            $html .= '<span style="color:#2e7d32">已生成</span> · ' . round(filesize($indexPath) / 1024, 1) . ' KB · 修改于 ' . date('Y-m-d H:i:s', filemtime($indexPath));
        } else {
            $html .= '<span style="color:#c62828">尚未生成</span>';
        }
        $html .= '</div>';

        $html .= '<div><b>llms-full.txt：</b>';
        if ($fullExists) {
            $html .= '<span style="color:#2e7d32">已生成</span> · ' . round(filesize($fullPath) / 1024, 1) . ' KB · 修改于 ' . date('Y-m-d H:i:s', filemtime($fullPath));
        } else {
            $html .= '<span style="color:#c62828">尚未生成</span>';
        }
        $html .= '</div>';

        if ($status && !empty($status['error'])) {
            $html .= '<div style="color:#c62828;margin-top:4px"><b>上次错误：</b>' . htmlspecialchars($status['error']) . '</div>';
        } elseif ($status && !empty($status['ok'])) {
            $triggerText = isset($status['trigger']) ? '（' . $status['trigger'] . '）' : '';
            $html .= '<div style="color:#2e7d32;margin-top:4px">上次生成成功' . $triggerText . '：' . date('Y-m-d H:i:s', $status['time']) . '</div>';
        }

        $html .= '<div style="margin-top:10px">';
        $html .= '<a href="' . htmlspecialchars($actionUrl) . '" style="display:inline-block;background:#202020;color:#fff;text-decoration:none;padding:6px 14px;border-radius:6px;font-size:13px">立即重新生成</a>';
        $html .= '<span style="color:#888;margin-left:10px">提示：发布 / 更新文章时会自动更新；改了主题设置后点此按钮即可同步。</span>';
        $html .= '</div>';

        $html .= '</div>';
        return $html;
    }

    /**
     * 读取本插件自身的设置项。
     */
    private static function pluginOption($key, $default = '')
    {
        try {
            // Options::alloc()->plugin('Printerllm') 返回配置对象；
            // 直接访问缺失键会抛异常，这里捕获后回退到默认值，跨版本更稳。
            $cfg = Options::alloc()->plugin('Printerllm');
            $val = $cfg->$key;
            return ($val === null || $val === '') ? $default : $val;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * 确保 Printer 主题的 functions.php 已加载。
     *
     * regenerate() 在「插件激活」「发布文章钩子」「手动重生成」等 admin/action 上下文里执行，
     * 这些上下文不会渲染主题，Typecho 因此不会自动 require 主题的 functions.php，
     * 导致 printerPaperLlmPostUrl 等函数不可用、只能走 ?p=cid 降级链接。
     * 这里手动加载一次，让美化链接 / 主题原生 LLM 辅助函数生效。
     * 失败则静默，降级为最小化自包含实现。
     */
    private static function ensureThemeFunctions()
    {
        if (self::hasTheme()) {
            return;
        }
        try {
            $theme = Options::alloc()->theme;
            if (!$theme) {
                return;
            }
            $file = rtrim(str_replace('\\', '/', __TYPECHO_ROOT_DIR__), '/')
                  . '/usr/themes/' . $theme . '/functions.php';
            if (is_file($file)) {
                require_once $file;
            }
        } catch (\Throwable $e) {
            // 加载失败不影响主流程，降级使用自包含查询
        }
    }

    private static function themeOptions()
    {
        if (function_exists('printerPaperGetOptions')) {
            $o = printerPaperGetOptions(null);
            if ($o) {
                return $o;
            }
        }
        return Options::alloc();
    }
}