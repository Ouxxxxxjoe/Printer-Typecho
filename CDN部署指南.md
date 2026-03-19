# Printer 主题 CDN 缓存配置指南

本指南帮助你将 Printer 主题部署在 CDN（如 Cloudflare）上时，正确配置缓存策略，既保证网站性能，又确保动态功能正常工作。

---

## 快速概览

| 类型 | 建议缓存策略 | 说明 |
|------|-------------|------|
| 静态资源 | 长期缓存 | CSS、JS、图片、字体等 |
| HTML 页面 | 不缓存 | 包含动态访问统计，已设置禁用缓存头 |
| 搜索/筛选页面 | 不缓存 | 动态生成内容 |

---

## 静态资源缓存（强烈推荐开启）

以下文件类型可以安全地长期缓存，建议缓存时间：**30天或更长**

### 文件类型

```
.css      - 样式表（主题样式，必须缓存）
.js       - JavaScript 文件（如有，必须缓存）
.svg      - 矢量图标（主题图标，必须缓存）
.ico      - 网站图标（必须缓存）
.woff/.woff2 - 网页字体（视情况而定，见下方说明）
.ttf/.otf - 字体文件（视情况而定，见下方说明）
.png/.jpg/.jpeg/.gif - 图片（视情况而定，见下方说明）
```

### ⚠️ 关于远程资源缓存的重要说明

#### 图片（两种情况）

**场景 1：图片存储在本地（默认）**
- 图片路径如：`https://your-domain.com/usr/uploads/2024/01/image.jpg`
- **需要缓存** - 配置图片文件类型的缓存规则

**场景 2：使用外部图床（OSS/COS/又拍云等）**
- 图片路径如：`https://your-bucket.oss-cn-beijing.aliyuncs.com/image.jpg`
- **无需缓存** - 图片不经过你的 CDN，由图床服务商处理缓存

#### 字体（两种情况）

**场景 1：未使用云字体（默认）**
- 字体通过主题本地加载或系统默认字体
- **需要缓存** - 如果有本地字体文件，配置字体缓存规则

**场景 2：使用云字体（如中文网字计划）**
- 在主题设置中填写了「中文字体 CSS 链接」
- 字体从第三方服务加载（如 `https://chinese-font.netlify.app`）
- **无需缓存** - 字体不经过你的 CDN，由字体服务商处理缓存

**如何判断？**
- 打开网站 → 右键「检查」→ Network 标签 → 刷新页面
- 查看图片/字体的请求地址
- 如果域名是你的网站域名 → 需要缓存
- 如果域名是其他服务商 → 无需缓存

### 路径匹配规则

**最精简配置（使用外部图床 + 云字体）：**
```
*.css      # 主题样式，必须缓存
*.js       # 如有自定义 JS，必须缓存
*.svg      # 主题图标，必须缓存
*.ico      # 网站图标，必须缓存
```

**标准配置（本地图片，无云字体）：**
```
*.css
*.js
*.svg
*.ico
*.woff
*.woff2
*.ttf
*.otf
*.png
*.jpg
*.jpeg
*.gif
```

---

## 不缓存的路径（重要）

以下路径**不应该被缓存**，否则会导致功能异常：

### 1. 所有 PHP 页面（已设置禁用缓存头）

主题已在 `functions.php` 中设置了禁用缓存的 HTTP 头：

```php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
```

这意味着所有 `.php` 页面默认不会被 CDN 缓存。

### 2. 具体不缓存的路径

| 路径模式 | 原因 |
|---------|------|
| `*.php` | 动态生成，包含访问统计 |
| `/` | 首页包含动态统计数据 |
| `/index.php` | 首页 |
| `/archive/*` | 分类/标签归档页 |
| `/search/*` | 搜索结果页 |
| `/feed/` | RSS 订阅（动态生成） |
| `/admin/*` | **Typecho 后台 - 绝对不能缓存** |
| `/install.php` | Typecho 安装程序 |
| `/action/*` | Typecho 动作请求（登录、评论等） |

---

## 为什么 HTML 页面不能缓存？

Printer 主题包含以下动态功能，缓存会导致数据不更新：

### 1. 访问统计功能
- **总访问量** - 每次访问都会更新
- **今日访问量** - 每日自动清零并重新统计
- **数据存储** - 使用数据库实时记录

### 2. 随机阅读功能
- 每次页面加载随机显示不同文章
- 缓存会导致随机功能失效

### 3. 最新文章链接
- 首页显示最新文章链接
- 发布新文章后需要立即显示

---

## Cloudflare 具体配置

### 页面规则 (Page Rules) 配置示例

#### 规则 1：静态资源长期缓存

```
URL: *example.com/*.css
设置：
  - Cache Level: Cache Everything
  - Edge Cache TTL: 1 month
  - Browser Cache TTL: 1 month
```

```
URL: *example.com/*.js
设置：
  - Cache Level: Cache Everything
  - Edge Cache TTL: 1 month
  - Browser Cache TTL: 1 month
```

```
URL: *example.com/css/*
设置：
  - Cache Level: Cache Everything
  - Edge Cache TTL: 1 month
```

#### 规则 2：HTML 页面不缓存（保险设置）

```
URL: *example.com/*
设置：
  - Cache Level: Bypass
  - 或者使用 Origin Cache Control: 开启（让主题控制缓存）
```

### 推荐配置顺序（从上到下）

1. `*example.com/css/*` → Cache Everything, 30天
2. `*example.com/*.js` → Cache Everything, 30天
3. `*example.com/*.png` → Cache Everything, 30天
4. `*example.com/*` → Bypass（或遵循源站头）

### 使用 Cache Rules（新版 Cloudflare）

```json
{
  "rules": [
    {
      "name": "Cache Static Assets",
      "expression": "(http.request.uri.path contains \".css\") or (http.request.uri.path contains \".js\") or (http.request.uri.path contains \".png\") or (http.request.uri.path contains \".jpg\")",
      "action": {
        "cache": true,
        "edge_ttl": {
          "mode": "override_origin",
          "default": 2592000
        }
      }
    },
    {
      "name": "Bypass HTML",
      "expression": "(http.request.uri.path contains \".php\") or (http.request.uri.path eq \"/\")",
      "action": {
        "cache": false
      }
    }
  ]
}
```

---

## Cloudflare Cache Rules 表达式（推荐）

Cloudflare 新版 Cache Rules 使用表达式语法，以下是针对 Printer 主题的现成配置，直接复制使用。

### 规则 1：静态资源缓存（高优先级）

**使用外部图床或云字体的用户：** 请根据实际情况删除对应部分

**完整表达式（本地图片 + 本地字体）：**
```
(http.request.uri.path contains ".css") or
(http.request.uri.path contains ".js") or
(http.request.uri.path contains ".png") or
(http.request.uri.path contains ".jpg") or
(http.request.uri.path contains ".jpeg") or
(http.request.uri.path contains ".svg") or
(http.request.uri.path contains ".gif") or
(http.request.uri.path contains ".ico") or
(http.request.uri.path contains ".woff") or
(http.request.uri.path contains ".woff2") or
(http.request.uri.path contains ".ttf") or
(http.request.uri.path contains ".otf")
```

**精简表达式（外部图床 + 云字体）：**
```
(http.request.uri.path contains ".css") or
(http.request.uri.path contains ".js") or
(http.request.uri.path contains ".svg") or
(http.request.uri.path contains ".ico")
```

**操作设置：**
- 缓存：缓存
- Edge TTL：覆盖源站，1个月（2592000秒）
- Browser TTL：覆盖源站，1个月

### 规则 2：CSS 目录缓存（高优先级）

**表达式：**
```
(http.request.uri.path contains "/css/")
```

**操作设置：**
- 缓存：缓存
- Edge TTL：覆盖源站，1个月

### 规则 3：Typecho 关键路径不缓存（最高优先级）

**⚠️ 重要：以下路径绝对不能缓存，否则会导致后台无法访问！**

**表达式：**
```
(http.request.uri.path contains "/admin") or
(http.request.uri.path contains "/install.php") or
(http.request.uri.path contains "/action/")
```

**操作设置：**
- 缓存：不缓存
- 注意：这条规则应该放在最前面，优先级最高

### 规则 4：已登录用户不缓存（高优先级）

**说明：** Typecho 登录用户（管理员、编辑）访问前端时，不应缓存页面，确保能看到最新内容

**表达式：**
```
(http.cookie contains "__typecho_uid")
```

**操作设置：**
- 缓存：不缓存
- 说明：检测到 Typecho 登录 Cookie 时，直接回源，不缓存页面

**补充说明：**
- Typecho 登录后会设置 `__typecho_uid` Cookie（存储用户 ID）
- 同时还会设置 `__typecho_authCode`（存储登录凭证）
- 检测 `__typecho_uid` 即可判断用户是否登录
- 退出登录后这些 Cookie 会被删除，恢复缓存策略

### 规则 5：PHP 页面不缓存（低优先级）

**表达式：**
```
(http.request.uri.path contains ".php") or
(http.request.uri.path eq "/")
```

**操作设置：**
- 缓存：不缓存

### 简化版表达式（快速使用）

如果你希望更简洁，可以使用以下组合表达式：

**静态资源缓存 - 完整版（本地图片 + 本地字体）：**
```
(http.request.uri.path matches "\\.(css|js|png|jpg|jpeg|svg|gif|ico|woff|woff2|ttf|otf)$")
```

**静态资源缓存 - 精简版（外部图床 + 云字体）：**
```
(http.request.uri.path matches "\\.(css|js|svg|ico)$")
```

**动态页面不缓存（一条规则）：**
```
(not http.request.uri.path matches "\\.(css|js|png|jpg|jpeg|svg|gif|ico|woff|woff2|ttf|otf)$")
```

### 配置步骤

1. 登录 Cloudflare 控制台
2. 选择你的域名
3. 进入 **Caching** → **Cache Rules**
4. 点击 **Create rule**
5. 输入规则名称（如 "Cache Static Assets"）
6. 选择 **Expression Editor** 模式
7. 粘贴上面的表达式
8. 配置缓存操作
9. 点击 **Deploy**

### 优先级建议

按以下顺序排列规则（数字越小优先级越高）：

| 顺序 | 规则名称 | 表达式概要 | 说明 |
|------|---------|-----------|------|
| 1 | **Bypass Typecho Admin** | `path contains "/admin"` | **最高优先级，确保后台可用** |
| 2 | **Bypass Logged-in Users** | `cookie contains "__typecho_uid"` | 已登录用户不缓存 |
| 3 | Cache CSS Directory | `path contains "/css/"` | |
| 4 | Cache Static Assets | `path matches "\.(css\|js\|png\|...)$"` | |
| 5 | Bypass Dynamic | `path contains ".php" or path eq "/"` | |

### ⚠️ 重要提醒

**务必将 `/admin/*` 路径排除在缓存之外！** 如果 CDN 缓存了 Typecho 后台：
- 登录状态会失效，无法进入后台
- 保存设置可能不生效
- 出现各种奇怪的行为

建议在配置完 CDN 后，立即测试后台访问：`https://your-domain.com/admin/`

---

## 其他 CDN 配置参考

### 阿里云 CDN

```
缓存配置：
- 目录 /css/ 缓存 30 天
- 后缀 .js .css .png .jpg 缓存 30 天
- 根目录 / 不缓存
- .php 文件不缓存
- /admin/ 目录不缓存（重要！）
- /action/ 目录不缓存
```

### 腾讯云 CDN

```
节点缓存规则：
1. 文件类型：css|js|png|jpg|svg|woff2 缓存 30 天
2. 目录：/css/ 缓存 30 天
3. 全路径：/ 不缓存
4. 文件类型：php 不缓存
5. 目录：/admin/ 不缓存（重要！）
6. 目录：/action/ 不缓存
```

### 又拍云

```
缓存规则：
- /*.css 30 天
- /*.js 30 天
- /css/* 30 天
- /*.php 不缓存
- / 不缓存
- /admin/* 不缓存（重要！）
- /action/* 不缓存
```

---

## 验证缓存是否生效

### 方法 1：使用 curl 检查响应头

```bash
# 检查静态资源是否被缓存
curl -I https://your-domain.com/css/style.css

# 查看响应头中的缓存相关字段
# cf-cache-status: HIT 表示 CDN 缓存命中
# cache-control: max-age=2592000 表示缓存时间

# 检查 HTML 页面是否未缓存
curl -I https://your-domain.com/

# 应该看到：
# cache-control: no-store, no-cache, must-revalidate, max-age=0
# pragma: no-cache
```

### 方法 2：浏览器开发者工具

1. 打开浏览器开发者工具（F12）
2. 切换到 Network 标签
3. 刷新页面
4. 查看静态资源的 `cf-cache-status` 或 `x-cache` 头
   - `HIT` - CDN 缓存命中
   - `MISS` - CDN 缓存未命中
   - `BYPASS` - 跳过缓存

### 方法 3：观察访问统计

1. 打开网站首页
2. 刷新页面多次
3. 观察页脚访问统计数字是否正常增加
4. 如果数字不增加，说明页面可能被缓存了

---

## 常见问题

### Q: 为什么访问统计数字不更新？

A: 可能是 CDN 缓存了 HTML 页面。请检查：
1. CDN 是否遵循了源站的 `Cache-Control` 头
2. 页面规则是否正确配置为 Bypass PHP 页面
3. 浏览器本地缓存（尝试强制刷新 Ctrl+F5）

### Q: 配置 CDN 后无法登录 Typecho 后台？

A: 这是 `/admin/` 路径被缓存导致的。**立即检查：**
1. 在 CDN 规则中添加 `/admin/*` 不缓存的规则
2. 清除 CDN 缓存
3. 清除浏览器 Cookie
4. 重新访问 `https://your-domain.com/admin/`

**预防措施：** 配置 CDN 时，第一条规则就应该是排除 `/admin/` 路径！

### Q: 静态资源更新后，用户看到旧版本？

A: 这是正常的缓存行为。解决方法：
1. 在 CDN 控制台手动刷新缓存（Purge Cache）
2. 修改文件名或添加版本号参数，如 `style.css?v=2`
3. 等待缓存过期

### Q: 可以缓存 RSS 订阅吗？

A: 可以设置短时间缓存（5-15分钟），但不建议长时间缓存，因为新文章发布后订阅需要更新。

### Q: 使用了外部图床，还需要配置图片缓存吗？

A: **不需要。** 如果你的图片存储在阿里云 OSS、腾讯云 COS、又拍云等外部图床：
- 图片 URL 是图床域名（如 `https://your-bucket.oss-cn-beijing.aliyuncs.com`）
- 不经过你的 CDN，由图床服务商负责缓存
- 只需缓存主题本身的 CSS、JS 等资源

**建议：** 使用外部图床的用户，在配置 CDN 规则时移除图片相关的缓存配置，简化规则。

### Q: 使用了云字体（中文网字计划），还需要配置字体缓存吗？

A: **不需要。** 如果你在主题设置中配置了「中文字体 CSS 链接」：
- 字体从第三方服务（如中文网字计划）加载
- 不经过你的 CDN，由字体服务商负责缓存和分发
- 只需缓存主题本身的 CSS、JS、图标等资源

**建议：** 使用云字体的用户，在配置 CDN 规则时移除 `.woff`、`.woff2`、`.ttf`、`.otf` 等字体相关的缓存配置。

**最简配置示例（外部图床 + 云字体）：**
只需缓存 CSS、JS、SVG 图标和网站图标：
```
(http.request.uri.path matches "\\.(css|js|svg|ico)$")
```

### Q: 搜索页面可以被缓存吗？

A: 不建议。搜索结果是动态的，不同用户搜索不同关键词，缓存意义不大。

### Q: 管理员登录后，为什么前端页面还是缓存的？

A: 这是因为 CDN 没有区分登录用户和访客。建议添加「已登录用户不缓存」规则：

```
(http.cookie contains "__typecho_uid")
```

**作用：**
- 管理员、编辑登录后访问前端，始终看到最新内容
- 发布/修改文章后立即生效，无需等待缓存过期
- 访客（未登录用户）仍然享受缓存加速

**原理：**
- Typecho 登录后会设置 `__typecho_uid` Cookie（存储用户 ID）
- 检测到该 Cookie 存在，说明用户已登录，跳过缓存
- 退出登录后 Cookie 被删除，恢复正常的缓存策略

**注意：** 这条规则优先级应该仅次于 `/admin/` 规则。

---

## 最佳实践总结

1. **静态资源**：长期缓存（30天+），提高加载速度
2. **HTML 页面**：不缓存，确保动态数据实时更新
3. **遵循源站头**：让 CDN 遵循主题设置的 `Cache-Control` 头
4. **定期清理**：更新静态资源后手动清理 CDN 缓存
5. **监控统计**：定期检查访问统计是否正常更新

---

## 技术支持

如有问题，请访问主题主页：https://github.com/Ouxxxxxjoe/Printer-Typecho
